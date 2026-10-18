<?php

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

?>