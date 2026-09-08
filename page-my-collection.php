<?php
/**
 * Template Name: My Collection
 * Template Post Type: page
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( have_posts() ) {
    the_post();
}
?>

<div class="d-flex flex-column flex-md-row w-100">
    <div class="site-main flex-fill">
        <section id="body-content" class="body-section text-center">
            <header class="page-header">
                <h1 class="page-title">
                    <?php the_title(); ?>
                </h1>

                <?php if ( has_excerpt() ) : ?>
                    <div class="archive-description">
                        <?php the_excerpt(); ?>
                    </div>
                <?php endif; ?>
            </header>
            <?php 
            if (!is_user_logged_in()) {
                
                $login_url = wp_login_url(home_url('/my-collection/'));

                echo '<p class="has-white-color">Please <a href="' .
                    esc_url($login_url) .
                    '" style="color: white;">log in</a> to view your collection.</p>';
                
            } else {
             ?>
                <div class="archive-posts">
                <main
                    id="collection-inventory"
                    class="tci"
                    aria-label="<?php esc_attr_e(
                        'My comic collection',
                        'collectibles'
                    ); ?>"
                >
                    <?php
                    if (
                        function_exists(
                            'tcs_inventory_render_app'
                        )
                    ) {
                        tcs_inventory_render_app();
                    } else {
                        ?>
                        <div class="tci-empty">
                            <h2>My collection</h2>

                            <p>
                                The collection inventory module
                                needs to be enabled.
                            </p>
                        </div>
                        <?php
                    }
                    ?>
                </main>
            </div>
        <?php } ?>
        </section>
    </div>
</div>

<?php get_footer(); ?>