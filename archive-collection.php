<?php get_header(); ?>

<div class="d-flex flex-column flex-md-row w-100">
    <main class="site-main flex-fill">
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

                <?php
                $selected_publisher = isset($_GET['publisher']) ? sanitize_text_field($_GET['publisher']) : 'all';

                $publishers = get_terms([
                        'taxonomy' => 'publisher',
                        'parent' => 0,
                        'hide_empty' => false,
                    ]);
                ?>

                <?php if (!empty($publishers)) : ?>

                    <div class="collection-filters mb-4">
                        <a href="?publisher=all" class="btn">All</a>

                        <?php foreach ($publishers as $publisher): ?>
                            <a href="?publisher=<?php echo esc_attr($publisher->slug); ?>" class="btn">
                                <?php echo esc_html($publisher->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>   

                    <div class="collection-by-publisher">
                        <?php foreach ($publishers as $publisher) :
                            // Filter logic
                            if ($selected_publisher !== 'all' && $selected_publisher !== $publisher->slug) {
                                continue;
                            }
                            // Get child terms (series)
                            $series_terms = get_terms([
                                'taxonomy' => 'publisher',
                                'parent' => $publisher->term_id,
                                'hide_empty' => true,
                            ]);

                            if (empty($series_terms)) continue;

                            ?>
                            <div class="publisher-group mb-5">
                                <h2 class="publisher-title"><?php echo esc_html($publisher->name); ?></h2>
                                    <div class="series-grid">
                                        <?php foreach ($series_terms as $series): ?>
                                            <div class="series-card">
                                                <a href="<?php echo esc_url(get_term_link($series)); ?>">
                                                    <?php echo esc_html($series->name); ?>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                            </div>
                        <?php endforeach; ?>

                    </div>                
 
                    <div class="nav text-center justify-content-center py-4"> 
                                <?php
                                the_posts_pagination( array(
                                    'mid_size'           => 2,
                                    'prev_text'          => __('« Previous'),
                                    'next_text'          => __('Next »'),
                                    'screen_reader_text' => __('Posts navigation'),
                                    'aria_label'         => __('Posts'),
                                    'class'              => 'pagination',
                                ) );
                                ?>                     
                    </div>                    
            </div>
                <?php else : ?>
                    <article class="no-results not-found">
                        <header class="entry-header">
                            <h2 class="page-title"><?php esc_html_e('Nothing Found', 'collectibles'); ?></h2>
                        </header>
                        <div class="entry-content">
                            <p><?php esc_html_e('Add some comics to your collection!', 'collectibles'); ?></p>
                        </div>
                    </article>
                <?php endif;    
            }
            ?> 
        </section>    
    </main>
</div>

<?php get_footer(); ?>