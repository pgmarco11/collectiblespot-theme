<?php
/**
 * Default single-post template.
 */
$collectibles_category = get_category_by_slug( 'collectibles' );
$auctions_category     = get_category_by_slug( 'auctions' );

$collectibles_parent_id = (
    $collectibles_category &&
    ! is_wp_error( $collectibles_category )
)
    ? (int) $collectibles_category->term_id
    : 0;

$auctions_parent_id = (
    $auctions_category &&
    ! is_wp_error( $auctions_category )
)
    ? (int) $auctions_category->term_id
    : 0;

$is_collectibles_content = false;

if ( $collectibles_parent_id ) {
    $is_collectibles_content =
        in_category( $collectibles_parent_id ) ||
        post_is_in_descendant_category(
            $collectibles_parent_id
        );
}
if (
    ! $is_collectibles_content &&
    $auctions_parent_id
) {
    $is_collectibles_content =
        in_category( $auctions_parent_id ) ||
        post_is_in_descendant_category(
            $auctions_parent_id
        );
}

if ( $is_collectibles_content ) {
    get_template_part( 'parts/header', 'collectibles' );
} else {
    get_header();
}

// Start the main WordPress loop to display post content
if ( have_posts() ) : ?>

    <?php while ( have_posts() ) : ?>
        <?php the_post(); ?>

        <article
            id="post-<?php the_ID(); ?>"
            <?php post_class(); ?>
        >
            <nav
                class="category-breadcrumbs"
                aria-label="<?php esc_attr_e(
                    'Breadcrumb',
                    'collectibles'
                ); ?>"
            >
                <?php
                $categories = get_the_category();

                if (
                    $categories &&
                    ! is_wp_error( $categories )
                ) {
                    $deepest  = null;
                    $max_depth = -1;

                    foreach ( $categories as $category ) {
                        $depth     = 0;
                        $parent_id = (int) $category->parent;

                        while ( $parent_id ) {
                            $parent = get_category( $parent_id );

                            if (
                                ! $parent ||
                                is_wp_error( $parent )
                            ) {
                                break;
                            }

                            $depth++;
                            $parent_id = (int) $parent->parent;
                        }

                        if ( $depth > $max_depth ) {
                            $max_depth = $depth;
                            $deepest   = $category;
                        }
                    }

                    $breadcrumb_categories = [];

                    while (
                        $deepest &&
                        ! is_wp_error( $deepest )
                    ) {
                        $breadcrumb_categories[] = $deepest;

                        if ( ! $deepest->parent ) {
                            break;
                        }

                        $deepest = get_category(
                            $deepest->parent
                        );
                    }

                    $breadcrumb_categories = array_slice(
                        array_reverse(
                            $breadcrumb_categories
                        ),
                        0,
                        3
                    );

                    foreach (
                        $breadcrumb_categories as $index => $category
                    ) {
                        if ( $index > 0 ) {
                            ?>
                            <span
                                class="separator"
                                aria-hidden="true"
                            >➤</span>
                            <?php
                        }
                    
                        $category_url = get_category_link(
                            $category->term_id
                        );
                    
                        if ( 0 === $index ) {
                            /*
                             * The root category is a direct link so it receives
                             * the orange comic-catalog breadcrumb style.
                             */
                            ?>
                            <a href="<?php echo esc_url( $category_url ); ?>">
                                <?php echo esc_html( $category->name ); ?>
                            </a>
                            <?php
                        } else {
                            ?>
                            <span class="category">
                                <a href="<?php echo esc_url( $category_url ); ?>">
                                    <?php echo esc_html( $category->name ); ?>
                                </a>
                            </span>
                            <?php
                        }
                    }

                    if ( $breadcrumb_categories ) {
                        ?>
                        <span
                            class="separator"
                            aria-hidden="true"
                        >➤</span>
                        <?php
                    }
                }
                ?>

                <span
                    class="current-category"
                    aria-current="page"
                >
                    <?php the_title(); ?>
                </span>
            </nav>

            <h1 class="mb-3 page-title">
                <?php the_title(); ?>
            </h1>

            <?php
            if ( $is_collectibles_content ) {
                get_template_part(
                    'parts/single',
                    'collectibles'
                );
            } else {
                get_template_part(
                    'parts/single',
                    'default'
                );
            }
            ?>
        </article>

    <?php endwhile; ?>

<?php else : ?>

    <p>
        <?php esc_html_e(
            'Sorry, no content found.',
            'collectibles'
        ); ?>
    </p>

<?php endif; ?>

<?php
if ( $is_collectibles_content ) :
    ?>
    </section>
    </main>

    <?php
    $sidebar = get_field( '_show_sidebar' );

    if ( $sidebar !== false ) {
        get_sidebar();
    }
endif;
?>

</div>

<?php get_footer(); ?>