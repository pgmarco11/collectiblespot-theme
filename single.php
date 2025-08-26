<?php
// Get category IDs for 'collectibles', 'auctions', and 'collection' using their slugs
$collectibles_parent_id = get_category_by_slug('collectibles')->term_id;
$auctions_parent_id = get_category_by_slug('auctions')->term_id;
$collection_parent_id = get_category_by_slug('collection')->term_id;

// Determine which header to display based on post category
if (
    post_is_in_descendant_category($collectibles_parent_id) || in_category($collectibles_parent_id) ||
    post_is_in_descendant_category($auctions_parent_id) || in_category($auctions_parent_id)
) {
    // Load collectibles-specific header for posts in collectibles or auctions categories
    get_template_part('parts/header', 'collectibles');
} elseif (
    post_is_in_descendant_category($collection_parent_id) || in_category($collection_parent_id)
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
            post_is_in_descendant_category($collection_parent_id) || in_category($collection_parent_id)
        ) {
            // Load collection-specific template part for collection category posts
            get_template_part('parts/single', 'collection');
        } else {
            ?>
            <!-- Start article container with post ID and classes -->
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <!-- Breadcrumb navigation -->
                <nav class="breadcrumb mb-4">        
                    <?php
                    // Get all categories for the current post
                    $categories = get_the_category();

                    // Build breadcrumb trail if categories exist
                    if ($categories && !is_wp_error($categories)) {
                        $deepest = null;
                        $max_depth = 0;

                        // Find the deepest category in the hierarchy
                        foreach ($categories as $cat) {
                            $depth = 0;
                            $parent = $cat->parent;
                            // Calculate category depth by traversing parent categories
                            while ($parent != 0) {
                                $depth++;
                                $parent = get_category($parent)->parent;
                            }
                            if ($depth > $max_depth) {
                                $max_depth = $depth;
                                $deepest = $cat;
                            }
                        }

                        // Build array of categories for breadcrumb trail
                        $breadcrumb_cats = [];
                        while ($deepest) {
                            $breadcrumb_cats[] = $deepest;
                            if ($deepest->parent == 0) break;
                            $deepest = get_category($deepest->parent);
                        }

                        // Reverse and limit to 3 categories for breadcrumbs
                        $breadcrumb_cats = array_reverse($breadcrumb_cats);
                        $breadcrumb_cats = array_slice($breadcrumb_cats, 0, 3);

                        // Output breadcrumb links
                        foreach ($breadcrumb_cats as $cat) {
                            echo '<a class="breadcrumb-item" href="' . esc_url(get_category_link($cat->term_id)) . '">' . esc_html($cat->name) . '</a>';
                        }
                    }
                    ?>
                    <!-- Current page title in breadcrumb -->
                    <span class="breadcrumb-item active" aria-current="page"><?php the_title(); ?></span>
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
 elseif (post_is_in_descendant_category($collection_parent_id) || in_category($collection_parent_id)): ?>

    </section>
    </main>

<?php endif; ?>

<!-- Close main content wrapper -->
</div>

<?php
// Load footer template
get_footer();
?>