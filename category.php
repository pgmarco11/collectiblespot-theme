<?php get_header(); ?>

<div class="d-flex flex-column flex-md-row w-100">

    <main class="site-main flex-fill">
        <section id="body-content" class="body-section text-center">
            <header class="page-header">
                <?php
                    if (is_category()) {
                        $category = get_queried_object();
                        $breadcrumbs = [];

                        while ($category->parent != 0) {
                            $category = get_category($category->parent);
                            array_unshift($breadcrumbs, $category);
                        }
                        
                        $current = get_queried_object();

                        echo '<div class="category-breadcrumbs">';
                        foreach ($breadcrumbs as $crumb) {
                            echo '<a href="' . get_category_link($crumb->term_id) . '">' . $crumb->name . '</a> <span class="separator">&#10148;</span> ';
                        }
                        echo '<span class="current-category">' . $current->name . '</span>';
                        echo '</div>';
                    }           

                $the_archive_title = get_the_archive_title();
                $the_archive_title = str_replace('Category: ', '', $the_archive_title);

                echo '<h1 class="page-title">' . $the_archive_title . '</h1>';
                the_archive_description('<div class="archive-description">', '</div>'); 
                ?>  
            </header>

            <?php if (have_posts()) : ?>
                <div class="archive-posts">
                <?php while (have_posts()) : the_post(); 
                $ebay_price = get_post_meta(get_the_ID(), 'ebay_price', true);
                $buy_now_price = get_post_meta(get_the_ID(), 'ebay_buy_now_price', true);
                

                 if($buy_now_price || $ebay_price) {
                    $url = get_post_meta(get_the_ID(), 'ebay_url', true);
                    $image_url = get_post_meta(get_the_ID(), 'ebay_image_url', true);
                } else {
                    $discogs_price = get_post_meta(get_the_ID(), 'discogs_price', true);    
                    $image_url = get_post_meta(get_the_ID(), 'discogs_image_uri', true);            
                    $url = get_post_meta(get_the_ID(), 'discogs_url', true);
                }         
                    
                ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <div class="entry-wrapper" style="display: flex; gap: 20px; align-items: flex-start;">
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
                                      <?php 
                                        
                                        $bids = get_post_meta(get_the_ID(), 'ebay_bid_count', true);
                                        $date_string = get_post_meta(get_the_ID(), 'ebay_end_date', true);

                                        $ebay_id = get_field('ebay_item_id');
                                      
                                        $date = new DateTime($date_string, new DateTimeZone('UTC')); 
                                        $date->setTimezone(new DateTimeZone('America/New_York'));
                                                          
                                        $end_time = $date->format('g:i A T');
                                        $end_date = $date->format('Y-m-d'); 
                                      
                                      echo '<p>';
                                        echo (is_numeric($bids)) ? '<span class="fw-bold">Bids: </span>' . $bids . '<br>' : '';      

                                        echo $ebay_price ? '<span class="fw-bold">Price: </span>' .  esc_html($ebay_price) . '<br>' : 
                                            '<span class="fw-bold">Price: </span>' .  esc_html($discogs_price ? $discogs_price : '')  . '<br>'; 

                                        echo $buy_now_price ? '<span class="fw-bold">Buy Now Price: </span>' .  esc_html($buy_now_price) . '<br>' : '';                             
                                        echo $date_string ? '<span class="fw-bold">End Date: </span>' . esc_html($end_date ? $end_date : '') . ' ' . esc_html($end_time ? $end_time : '') . '<br>' : '';      
                                      echo '</p>';

                                      ?> 
                                </div>

                                <div class="d-flex justify-content-start">
                                    <a href="<?= $url ?>" class="btn btn-primary comic-bubble" target="_blank"> 
                                        <?php 
                                 
                                        echo ($buy_now_price || $ebay_price) ? ($buy_now_price ? 'Bid Now' : 'Buy on Ebay') : "Buy on Discogs";                                         
                                                                                   
                                        ?>
                                    </a>
                                    <a href="<?= the_permalink(); ?>" class="btn btn-secondary">View Details</a>
                                   <?php if(is_user_logged_in()): ?>
                                    <button class="add-to-wishlist"
                                        data-type="post"        
                                        data-item-id="<?php the_ID(); ?>"
                                        data-ebay-id="<?php echo esc_attr($ebay_id); ?>"
                                        data-item-url="<?php echo esc_url($url); ?>"
                                        data-title="<?php the_title(); ?>"
                                        data-image-url="<?php echo esc_url($image_url); ?>">
                                        Add to Wishlist
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
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
                            <p><?php esc_html_e('Sorry, no posts matched your criteria.', 'collectibles'); ?></p>
                        </div>
                    </article>
                <?php endif; ?>        
        </section>    
    </main>

    <?php get_sidebar(); ?>

</div>

<?php get_footer(); ?>
