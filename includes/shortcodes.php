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

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();   
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
    return $output;
}
add_shortcode('collectible_posts', 'collectible_posts_shortcode');

add_shortcode('tcs_free_plan_action', static function (): string {
    if (is_user_logged_in()) {
        $url = home_url('/my-collection/');
        $label = __('Back to My Collection', 'collectibles');
    } else {
        $url = home_url('/register/');
        $label = __('Start Free', 'collectibles');
    }

    return sprintf(
        '<div class="mt-auto pt-3 text-start"><a class="btn btn-primary tcs-free-plan-button" href="%s">%s</a></div>',
        esc_url($url),
        esc_html($label)
    );
});

add_shortcode('tcs_premium_plan_action', static function (): string {
    $checkout_url = home_url('/premium-checkout/');

    if (is_user_logged_in()) {
        $url = $checkout_url;
        $label = __('Upgrade to Premium', 'collectibles');
    } else {
        $url = wp_login_url($checkout_url);
        $label = __('Sign In to Upgrade', 'collectibles');
    }

    return sprintf(
        '<div class="mt-auto pt-3 text-start"><a class="btn btn-primary" href="%s">%s</a></div>',
        esc_url($url),
        esc_html($label)
    );
});