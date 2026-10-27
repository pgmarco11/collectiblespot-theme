<?php

add_filter('body_class', function ($classes) {
    if (is_page()) {
        $template = get_page_template_slug();
        $template_name = $template
            ? pathinfo($template, PATHINFO_FILENAME)
            : 'default';

        $slug = get_post_field('post_name', get_queried_object_id());

        if ($slug) {
            $classes[] = sanitize_html_class(
                'template-' . $template_name . '-' . $slug
            );
        }
    }

    return $classes;
});

/**
 * Include category names in the main front-end search.
 */
function tcs_search_include_categories($search, $query) {
    global $wpdb;

    if (
        is_admin() ||
        !$query->is_main_query() ||
        !$query->is_search() ||
        trim($search) === ''
    ) {
        return $search;
    }

    $term = $query->get('s');

    if (!is_string($term) || trim($term) === '') {
        return $search;
    }

    // Remove the leading AND so the existing search can be grouped.
    $original_search = preg_replace(
        '/^\s*AND\s+/i',
        '',
        $search,
        1,
        $replacements
    );

    if ($replacements !== 1 || $original_search === null) {
        return $search;
    }

    $category_search = $wpdb->prepare(
        "EXISTS (
            SELECT 1
            FROM {$wpdb->term_relationships} AS tcs_tr
            INNER JOIN {$wpdb->term_taxonomy} AS tcs_tt
                ON tcs_tt.term_taxonomy_id = tcs_tr.term_taxonomy_id
            INNER JOIN {$wpdb->terms} AS tcs_t
                ON tcs_t.term_id = tcs_tt.term_id
            WHERE tcs_tr.object_id = {$wpdb->posts}.ID
                AND tcs_tt.taxonomy = 'category'
                AND tcs_t.name LIKE %s
        )",
        '%' . $wpdb->esc_like(trim($term)) . '%'
    );

    $search = " AND (
        ({$original_search})
        OR {$category_search}
    ) ";

    // Category matches must also respect password protection.
    if (!is_user_logged_in()) {
        $search .= " AND ({$wpdb->posts}.post_password = '') ";
    }

    return $search;
}
add_filter('posts_search', 'tcs_search_include_categories', 10, 2);

function defer_scripts($tag, $handle, $src) {
    $defer_scripts = ['wp-emoji-release', 'jquery-migrate'];
    if (in_array($handle, $defer_scripts)) {
        return str_replace(' src', ' defer src', $tag);
    }
    return $tag;
}
add_filter('script_loader_tag', 'defer_scripts', 10, 3);

function tcs_hide_empty_main_menu_categories($items, $args) {
    if (is_admin() || ($args->theme_location ?? '') !== 'main') {
        return $items;
    }

    $category_ids = get_terms([
        'taxonomy' => 'category',
        'hide_empty' => true,
        'hierarchical' => true,
        'fields' => 'ids',
    ]);

    if (is_wp_error($category_ids)) {
        return $items;
    }

    $visible_categories = array_fill_keys(
        array_map('intval', $category_ids),
        true
    );

    $all = [];
    $kept = [];

    foreach ($items as $item) {
        $all[(int) $item->ID] = $item;

        $is_category = (
            $item->type === 'taxonomy' &&
            $item->object === 'category'
        );

        if (
            $is_category &&
            !isset($visible_categories[(int) $item->object_id])
        ) {
            continue;
        }

        $kept[(int) $item->ID] = $item;
    }

    // Preserve other links beneath a removed category.
    foreach ($kept as $item) {
        $parent = (int) $item->menu_item_parent;
        $seen = [(int) $item->ID => true];

        while ($parent && !isset($kept[$parent])) {
            if (!isset($all[$parent]) || isset($seen[$parent])) {
                $parent = 0;
                break;
            }

            $seen[$parent] = true;
            $parent = (int) $all[$parent]->menu_item_parent;
        }

        $item->menu_item_parent = (string) $parent;
    }

    // Update dropdown classes after removing empty categories.
    $parents = [];

    foreach ($kept as $item) {
        $parents[(int) $item->menu_item_parent] = true;
    }

    foreach ($kept as $item) {
        $item->classes = array_values(array_diff(
            (array) $item->classes,
            ['menu-item-has-children']
        ));

        if (isset($parents[(int) $item->ID])) {
            $item->classes[] = 'menu-item-has-children';
        }
    }

    return array_values($kept);
}

add_filter(
    'wp_nav_menu_objects',
    'tcs_hide_empty_main_menu_categories',
    20,
    2
);
?>