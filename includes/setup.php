<?php
// Theme setup
function u_setup_theme() {
    add_theme_support('title-tag');
    add_theme_support('custom-header');
    add_theme_support('menus');
    add_theme_support('widgets');
    add_theme_support('block-template-parts');
    add_theme_support('post-thumbnails');

    register_nav_menus([
        'main' => 'Main Menu'
    ]);

    add_editor_style([
        'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Lato:wght@100;300;400;700;900&display=swap',
        'includes/styles/editor.css'
    ]);
}
add_action('after_setup_theme', 'u_setup_theme');

// Theme settings admin page
function theme_add_settings_page() {
    add_menu_page(
        'Theme Settings',
        'Theme Settings',
        'manage_options',
        'theme-settings',
        'theme_settings_page_html',
        'dashicons-admin-generic',
        20
    );
}
add_action('admin_menu', 'theme_add_settings_page');

function theme_settings_page_html() {
    if (!current_user_can('manage_options')) return;

    if (isset($_POST['theme_settings_submit'])) {
        update_option('email_address', sanitize_email($_POST['email_address']));
        update_option('facebook_url', esc_url_raw($_POST['facebook_url']));
        update_option('x_url', esc_url_raw($_POST['x_url']));
        update_option('instagram_url', esc_url_raw($_POST['instagram_url']));
        update_option('youtube_url', esc_url_raw($_POST['youtube_url']));
    }

    $fields = [
        'email_address' => 'Email Address',
        'facebook_url'  => 'Facebook URL',
        'x_url'         => 'X URL',
        'instagram_url' => 'Instagram URL',
        'youtube_url'   => 'YouTube URL'
    ];
    ?>
    <div class="wrap">
        <h1>Theme Settings</h1>
        <form method="POST">
            <table class="form-table">
                <?php foreach ($fields as $field => $label): ?>
                    <tr>
                        <th scope="row"><label for="<?php echo $field; ?>"><?php echo $label; ?></label></th>
                        <td>
                            <input type="<?php echo $field === 'email_address' ? 'email' : 'url'; ?>"
                                   name="<?php echo $field; ?>"
                                   id="<?php echo $field; ?>"
                                   value="<?php echo esc_attr(get_option($field)); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <p class="submit">
                <button type="submit" name="theme_settings_submit" class="button button-primary">Save Changes</button>
            </p>
        </form>
    </div>
    <?php
}

// Widget areas
function collectibles_widgets_init() {
    $sidebars = [
        'primary-sidebar' => 'Primary Sidebar',
        'category-sidebar' => 'Category Sidebar'
    ];

    foreach ($sidebars as $id => $name) {
        register_sidebar([
            'name'          => __($name, 'collectibles'),
            'id'            => $id,
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ]);
    }
}
add_action('widgets_init', 'collectibles_widgets_init');

// Exclude pages from search results
function exclude_pages_from_search($query) {
    if (!is_admin() && $query->is_main_query() && $query->is_search()) {
        $exclude_ids = array_filter([
            intval(get_option('page_on_front')),
            3518
        ]);
        $query->set('post__not_in', $exclude_ids);
    }
}
add_action('pre_get_posts', 'exclude_pages_from_search');

// Custom rewrite rules
function custom_rewrite_rules() {
    add_rewrite_rule('^title/([0-9]+)/?', 'index.php?page_id=123&title_id=$matches[1]', 'top');
    add_rewrite_rule(
        '^comic-books/([^/]+)/([^/]+)/?',
        'index.php?pagename=comic-books&comic_issues_template=1&publisher_slug=$matches[1]&title_slug=$matches[2]',
        'top'
    );
}
add_action('init', 'custom_rewrite_rules');

add_action('init', function() {
    global $wp;
    $wp->add_query_var('publisher_id');
    $wp->add_query_var('search');
    $wp->add_query_var('page');
});

// Register custom query vars
function register_custom_query_vars($vars) {
    return array_merge($vars, [
        'publisher_slug',
        'title_slug',
        'title_id',
        'page' ,
        'letter'
    ]);
}
add_filter('query_vars', 'register_custom_query_vars');

// Load custom template
function load_comic_issues_template($template) {
    if (intval(get_query_var('comic_issues_template')) === 1) {
        $custom_template = get_template_directory() . '/page-comic-issues.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter('template_include', 'load_comic_issues_template');

//for single post category templates
function post_is_in_descendant_category($cats, $_post = null) {
    foreach ((array) $cats as $cat) {
        // Get all children of this category
        $descendants = get_term_children((int) $cat, 'category');
        if ($descendants && in_category($descendants, $_post)) {
            return true;
        }
    }
    return false;
}

// Function to convert eBay image URL to high-resolution
function get_high_res_ebay_image($image_url, $target_size = 's-l500') {
    // Check if the URL matches the expected eBay image pattern
    if (preg_match('/^(https:\/\/i\.ebayimg\.com\/images\/.*\/s-l)\d+(\.jpg)$/', $image_url)) {
            // Replace the size suffix (e.g., s-l140) with the high-res suffix (e.g., s-l1600)
            $high_res_url = preg_replace('/s-l\d+/', $target_size, $image_url);
            return $high_res_url;
    }
    return $image_url; // Return original if pattern doesn't match
 }
