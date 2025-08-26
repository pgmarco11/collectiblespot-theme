<?php
function search_join_categories($join) {
    global $wpdb;
    if (is_search()) {
        $join .= " LEFT JOIN $wpdb->term_relationships AS tr ON $wpdb->posts.ID = tr.object_id";
        $join .= " LEFT JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'category'";
        $join .= " LEFT JOIN $wpdb->terms AS t ON tt.term_id = t.term_id";
    }
    return $join;
}
add_filter('posts_join', 'search_join_categories');

function search_where_categories($where) {
    global $wpdb;
    if (is_search() && !is_admin()) {
        $search = get_query_var('s');
        if ($search) {
            $escaped = esc_sql($wpdb->esc_like($search));
            $where .= " OR (t.name LIKE '%$escaped%')";
        }
    }
    return $where;
}
add_filter('posts_where', 'search_where_categories');

function search_groupby_categories($groupby) {
    global $wpdb;
    if (is_search()) {
        $groupby = "{$wpdb->posts}.ID";
    }
    return $groupby;
}
add_filter('posts_groupby', 'search_groupby_categories');

?>