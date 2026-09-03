<?php get_header(); ?>

<div class="d-flex flex-column flex-md-row w-100">
    <div class="site-main flex-fill">
        <section id="body-content" class="body-section text-center">
            <header class="page-header">
                <?php

                        $post_type_obj = get_post_type_object('collection');
                        $singular_name = $post_type_obj ? $post_type_obj->labels->singular_name : 'Collection';

                        // Make it personal for the logged-in user
                        $the_archive_title = 'My ' . $singular_name;
                    
                ?>
                <h1 class="page-title"><?php echo $the_archive_title; ?></h1>
                <?php the_archive_description('<div class="archive-description">', '</div>'); ?>
            </header>
            <?php 
            if (!is_user_logged_in()) {
                echo '<p class="has-white-color">Please <a href="' . site_url('/login') . '" style="color: white;">log in</a> to view your collection.</p>';
            } else {
             ?>
                <div class="archive-posts">
                    <main id="collection-inventory" class="tci" aria-label="My comic collection">
                        <?php
                        if (function_exists('tcs_inventory_render_app')) {
                            tcs_inventory_render_app();
                        } else {
                            echo '<div class="tci-empty"><h1>My collection</h1><p>The collection inventory module needs to be enabled.</p></div>';
                        }
                        ?>
                    </main>
                </div>
            <?php } ?>
        </section>    
    </div>
</div>

<?php get_footer(); ?>