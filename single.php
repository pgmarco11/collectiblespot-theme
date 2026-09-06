<?php
// Get category IDs for 'collectibles', 'auctions', and 'collection' using their slugs
$collectibles_parent_id = get_category_by_slug('collectibles')->term_id;
$auctions_parent_id = get_category_by_slug('auctions')->term_id;

// Determine which header to display based on post category
if (
    post_is_in_descendant_category($collectibles_parent_id) || in_category($collectibles_parent_id) ||
    post_is_in_descendant_category($auctions_parent_id) || in_category($auctions_parent_id)
) {
    // Load collectibles-specific header for posts in collectibles or auctions categories
    get_template_part('parts/header', 'collectibles');
} elseif (
    get_post_type() === 'collection'
) {
    // Load collection-specific header for posts in collection category
    get_template_part('parts/header', 'collection');
} else {
    // Load default header for all other cases
    get_header();
}

// Start the main WordPress loop to display post content
if (have_posts()) :
    while (have_posts()) : the_post();

        // Check if post is in collection category or its descendants
        if (
            get_post_type() === 'collection'
        ) {
            // Load collection-specific template part for collection category posts
            get_template_part('parts/single', 'collection');
        } else {
            ?>
            <!-- Start article container with post ID and classes -->
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
             
                <!-- Breadcrumb navigation -->
            <nav
                class="category-breadcrumbs"
                aria-label="<?php esc_attr_e('Breadcrumb', 'collectibles'); ?>"
            >
                <?php
                $categories = get_the_category();

                if ($categories && ! is_wp_error($categories)) {
                    $deepest  = null;
                    $max_depth = -1;

                    /*
                    * Find the deepest assigned category.
                    */
                    foreach ($categories as $category) {
                        $depth     = 0;
                        $parent_id = $category->parent;

                        while ($parent_id) {
                            $parent = get_category($parent_id);

                            if (! $parent || is_wp_error($parent)) {
                                break;
                            }

                            $depth++;
                            $parent_id = $parent->parent;
                        }

                        if ($depth > $max_depth) {
                            $max_depth = $depth;
                            $deepest   = $category;
                        }
                    }

                    /*
                    * Build the category trail from the root category
                    * to the deepest assigned category.
                    */
                    $breadcrumb_categories = [];

                    while ($deepest && ! is_wp_error($deepest)) {
                        $breadcrumb_categories[] = $deepest;

                        if (! $deepest->parent) {
                            break;
                        }

                        $deepest = get_category($deepest->parent);
                    }

                    $breadcrumb_categories = array_reverse(
                        $breadcrumb_categories
                    );

                    /*
                    * Keep the breadcrumb reasonably short.
                    */
                    $breadcrumb_categories = array_slice(
                        $breadcrumb_categories,
                        0,
                        3
                    );

                    foreach ($breadcrumb_categories as $index => $category) {
                        if ($index > 0) {
                            ?>
                            <span class="separator" aria-hidden="true">➤</span>

                            <span class="category">
                                <a href="<?php echo esc_url(
                                    get_category_link($category->term_id)
                                ); ?>">
                                    <?php echo esc_html($category->name); ?>
                                </a>
                            </span>
                            <?php
                        } else {
                            ?>
                            <a href="<?php echo esc_url(
                                get_category_link($category->term_id)
                            ); ?>">
                                <?php echo esc_html($category->name); ?>
                            </a>
                            <?php
                        }
                    }

                    if ($breadcrumb_categories) {
                        ?>
                        <span class="separator" aria-hidden="true">➤</span>
                        <?php
                    }
                }
                ?>

                <span class="current-category" aria-current="page">
                    <?php the_title(); ?>
                </span>
            </nav>

                <!-- Post title -->
                <h1 class="mb-3 page-title"><?php the_title(); ?></h1>

                <?php
                // Load appropriate template part based on category
                if (
                    post_is_in_descendant_category($collectibles_parent_id) || in_category($collectibles_parent_id) ||
                    post_is_in_descendant_category($auctions_parent_id) || in_category($auctions_parent_id)
                ) {
                    // Load collectibles-specific template for collectibles/auctions posts
                    get_template_part('parts/single', 'collectibles');
                } else {
                    // Load default template for other posts
                    get_template_part('parts/single', 'default');
                }
                ?>

            </article>
            <?php
        }

    endwhile;
else :
    // Display message if no posts are found
    ?>
    <p><?php esc_html_e('Sorry, no content found.', 'collectibles'); ?></p>
<?php endif; ?>

<?php
// Close section and main tags for specific categories with sidebar
if (
    post_is_in_descendant_category($collectibles_parent_id) || in_category($collectibles_parent_id) ||
    post_is_in_descendant_category($auctions_parent_id) || in_category($auctions_parent_id) ):
    ?>
    </section>
    </main>

    <!-- Sidebar -->
    <?php
        // Check if sidebar should be displayed using Advanced Custom Fields (ACF)
        $sidebar = get_field('_show_sidebar');
        if ($sidebar !== false) {
            // Load sidebar if enabled
            get_sidebar();
        }
    ?>
<?php
 // Close section and main tags for specific categories without sidebar
 elseif (get_post_type() === 'collection'): ?>

    </section>
    </main>

<?php endif; ?>

<!-- Close main content wrapper -->
</div>

<?php
// Load footer template
get_footer();
?>