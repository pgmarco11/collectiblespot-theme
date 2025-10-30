<?php

function u_enqueue() {
    $manifest_path = get_template_directory() . '/public/manifest.json';

    if (file_exists($manifest_path)) {
        $manifest = json_decode(file_get_contents($manifest_path), true);
    } else {
        $manifest = [];
    }

    // Enqueue Google Fonts
    wp_register_style(
        'collectible-comic-fonts', 
        'https://fonts.googleapis.com/css2?family=Bangers&family=Bebas+Neue&family=Lato:wght@100;300;400;700;900&display=swap',
        [],
        null
    );
    wp_enqueue_style('collectible-comic-fonts');

    // Preload Google Fonts for performance
    add_action('wp_head', function () {
        echo '<link rel="preload" href="https://fonts.googleapis.com/css2?family=Bangers&family=Bebas+Neue&family=Lato:wght@100;300;400;700;900&display=swap" as="style" crossorigin>';
    }, 1);

    // Enqueue Bootstrap CSS
    wp_register_style(
        'bootstrap-css', 
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
        [],
        '5.3.2'
    );
    wp_enqueue_style('bootstrap-css');

    // Preload Bootstrap CSS
    add_action('wp_head', function () {
        echo '<link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" as="style" crossorigin>';
    }, 1);

    // Enqueue Bootstrap Icons CSS
    wp_register_style(
        'bootstrap-icons', 
        'https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css',
        [], 
        '1.10.0'
    );
    wp_enqueue_style('bootstrap-icons');

    // Preload Bootstrap Icons font
    add_action('wp_head', function () {
        echo '<link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/fonts/bootstrap-icons.woff2?e34853135f9e39acf64315236852cd5a" as="font" type="font/woff2" crossorigin>';
    }, 1);

    // Enqueue theme styles
    if (isset($manifest['index.css'])) {
        wp_enqueue_style('collectibles-style', get_template_directory_uri() . '/public/' . $manifest['index.css'], ['bootstrap-css'], null);
    }

    // Enqueue Bootstrap JS
    wp_enqueue_script(
        'bootstrap-js',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
        [],
        '5.3.2',
        true
    );

    // Enqueue theme scripts
    if (isset($manifest['index.js'])) {
        wp_enqueue_script('collectibles-script', get_template_directory() . '/public/' . $manifest['index.js'], ['bootstrap-js'], null, true);
    }
}

// Defer Bootstrap JS
add_filter('script_loader_tag', function ($tag, $handle, $src) {
    if ('bootstrap-js' === $handle) {
        $tag = str_replace(' src=', ' defer src=', $tag);
    }
    return $tag;
}, 10, 3);

add_action('wp_enqueue_scripts', 'u_enqueue');