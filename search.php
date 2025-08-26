<?php get_header(); ?>

<div class="d-flex flex-column flex-md-row w-100">

    <main class="site-main flex-fill">
        <section id="body-content" class="body-section text-center">
            <header class="page-header">
                <?php
                $search_query = get_search_query();
                echo '<h1 class="page-title">Search Results for: <span>' . esc_html($search_query) . '</span></h1>';
                ?>
            </header>

            <?php if (have_posts()) : ?>
                <div class="archive-posts">
                    <?php while (have_posts()) : the_post(); 
                        
                        $ebay_price = get_post_meta(get_the_ID(), 'ebay_price', true);
                        $buy_now_price = get_post_meta(get_the_ID(), 'ebay_buy_now_price', true);        
        
                        if($buy_now_price || $ebay_price) {
                            $url = get_post_meta(get_the_ID(), 'ebay_url', true);
                        } else {
                            $discogs_price = get_post_meta(get_the_ID(), 'discogs_price', true);                
                            $url = get_post_meta(get_the_ID(), 'discogs_url', true);
                        }
        
        
                    ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                            <div class="entry-wrapper d-flex gap-3 align-items-start">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="entry-thumbnail">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('medium'); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <div class="entry-content">
                                    <header class="entry-header">
                                        <?php the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>'); ?>
                                    </header>

                                    <div class="entry-summary">
                                        <?php the_excerpt(); ?>                                   
                                    </div>
                   
                                    <?php if ($url): ?>
                                    <div class="d-flex justify-content-start">
                                        <a href="<?= esc_url($url) ?>" class="btn btn-primary comic-bubble" target="_blank">
                                            <?php 
                                            echo ($buy_now_price || $ebay_price) ? ($buy_now_price ? 'Bid Now' : 'Buy on Ebay') : "Buy on Discogs"; 
                                            ?>
                                        </a>
                                        <a href="<?= the_permalink(); ?>" class="btn btn-secondary">View Details</a>
                                    </div>
                                    <?php endif; ?>                                
                            </div>
                        </article>
                    <?php endwhile; ?>
                    
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
                        <p><?php esc_html_e('Sorry, but nothing matched your search terms. Please try again with different keywords.', 'collectibles'); ?></p>
                        <?php get_search_form(); ?>
                    </div>
                </article>
            <?php endif; ?>        
        </section>    
    </main>

    <?php get_sidebar(); ?>

</div>

<?php get_footer(); ?>
