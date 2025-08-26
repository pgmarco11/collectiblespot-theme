<?php

// Shortcode to display different posts categories in collectibles
function collectible_posts_shortcode($atts) {
    // Define default attributes
    $atts = shortcode_atts(
        array(
            'child_category' => 'other',
            'number' => 3,
        ),
        $atts,
        'collectible_posts'
    );

    // Sanitize attributes
    $child_category = sanitize_text_field($atts['child_category']);
    $number = intval($atts['number']);

    // Set up WP_Query arguments
    $args = array(
        'post_type' => 'post', // Adjust if using a custom post type (e.g., 'ebay_auction')
        'posts_per_page' => $number,
        'tax_query' => array(
            array(
                'taxonomy' => 'category', // Adjust if using a custom taxonomy
                'field' => 'slug',
                'terms' => $child_category,
            ),
        ),
        'post_status' => 'publish',
    );

    // Run the query
    $query = new WP_Query($args);

    // Initialize output
    $output = '<div class="collectible-grid">';

    // Initialize post count
    $post_count = 0;

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_count++;

            $ebay_price = get_post_meta(get_the_ID(), 'ebay_price', true);            
    
            if ($ebay_price) {
                $url = get_post_meta(get_the_ID(), 'ebay_url', true);
            } else {
                $discogs_price = get_post_meta(get_the_ID(), 'discogs_price', true);
                $url = get_post_meta(get_the_ID(), 'discogs_url', true);
            }
    
            $price = $ebay_price ?: $discogs_price;
    
            $output .= '<article class="collectible-post">';
            if (has_post_thumbnail()) {
                $output .= '<div class="post-thumbnail"><a href="' . esc_url(get_permalink()) . '">' . get_the_post_thumbnail(null, 'medium') . '</a></div>';
            }
    
            $output .= '<h4><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h4>';
            $output .= '<div class="post-content"><span class="fw-bold">Price: </span>' . esc_html($price) . '<br></div>';
            $output .= '<p><a href="' . esc_url($url) . '" target="_blank" class="post-buy-button">Buy Now</a></p>';
            $output .= '</article>';
        }
    } else {
        $output .= '<p>No posts found for category: ' . esc_html($child_category) . '</p>';
    }

    // Reset post data
    wp_reset_postdata();

    $output .= '</div>';

    // Log the post count for debugging
    error_log("collectible_posts_shortcode: Found $post_count posts for category '$child_category'");

    return $output;
}
add_shortcode('collectible_posts', 'collectible_posts_shortcode');