<?php
defined('ABSPATH') || exit;

function collectibles_posts_pagination(array $args = []): void {
    global $wp_query;

    $total = max(
        1,
        (int) $wp_query->max_num_pages
    );

    $current = max(
        1,
        (int) get_query_var('paged', 1)
    );

    if ($total <= 1) {
        return;
    }

    $args = wp_parse_args($args, [
        'mid_size' => 2,
        'prev_text' => __('Previous', 'collectibles'),
        'next_text' => __('Next', 'collectibles'),
        'screen_reader_text' => __(
            'Posts navigation',
            'collectibles'
        ),
        'aria_label' => __('Posts', 'collectibles'),
    ]);

    $args['total'] = $total;
    $args['current'] = $current;
    $args['type'] = 'plain';

    echo '<nav class="navigation pagination" aria-label="'
        . esc_attr($args['aria_label']) . '">';

    echo '<h2 class="screen-reader-text">'
        . esc_html($args['screen_reader_text'])
        . '</h2>';

    echo '<div class="nav-links">';

    if ($current > 1) {
        echo '<a class="page-numbers" href="'
            . esc_url(get_pagenum_link(1))
            . '">'
            . esc_html__('First', 'collectibles')
            . '</a> ';
    }

    echo paginate_links($args);

    if ($current < $total) {
        echo ' <a class="page-numbers" href="'
            . esc_url(get_pagenum_link($total))
            . '">'
            . esc_html__('Last', 'collectibles')
            . '</a>';
    }

    echo '</div></nav>';
}