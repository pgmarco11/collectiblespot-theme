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
                        echo '<a href="' . get_category_link($crumb->term_id) . '">' . $crumb->name . '</a> <span class="separator">➤</span> ';
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
            <?php 
            if (!is_user_logged_in()) {
                echo '<p class="has-white-color">Please log in to view your collection.</p>';
            } else {
                if (have_posts()) : ?>
                    <div class="archive-posts">
                    <?php while (have_posts()) : the_post(); ?>                    
                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                            <div class="entry-wrapper" style="display: flex; gap: 20px; align-items: flex-start;">
                                <?php 

                                    $thumbnail_id = get_post_thumbnail_id();
                                    if ($thumbnail_id) {
                                        error_log('Post ' . get_the_ID() . ' has thumbnail ID: ' . $thumbnail_id);
                                        $thumbnail_url = wp_get_attachment_url($thumbnail_id);
                                        error_log('Thumbnail URL for post ' . get_the_ID() . ': ' . ($thumbnail_url ? $thumbnail_url : 'No URL found'));
                                    } else {
                                        error_log('No thumbnail found for post ' . get_the_ID());
                                        // Log additional metadata for debugging
                                        $image_url = get_post_meta(get_the_ID(), 'image_url', true);
                                        error_log('Stored image_url meta for post ' . get_the_ID() . ': ' . ($image_url ? $image_url : 'No image_url meta'));
                                    }
                                    ?>
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="entry-thumbnail">
                                            <a href="<?php the_permalink(); ?>">
                                            <?php 
                                                the_post_thumbnail('medium', ['class' => 'attachment-medium']);
                                                $image_data = wp_get_attachment_image_src($thumbnail_id, 'medium');
                                                error_log('Thumbnail displayed for post ' . get_the_ID() . ' - Size: medium, URL: ' . ($image_data ? $image_data[0] : 'No image data'));
                                                ?>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="entry-thumbnail">                                         
                                            <?php error_log('No featured image available for post ' . get_the_ID()); ?>
                                            <!-- Display placeholder image if available -->
                                            <?php
                                            $placeholder_url = defined('PUBLISHER_PLACEHOLDER_IMAGE_URL') ? PUBLISHER_PLACEHOLDER_IMAGE_URL : '';
                                            ?>
                                            <a href="<?php the_permalink(); ?>">
                                            <?php
                                                if ($image_url && filter_var($image_url, FILTER_VALIDATE_URL)) {
                                                    echo '<img src="' . esc_url($image_url) . '" alt="Comic Image" class="attachment-medium">';
                                                    error_log('Displayed meta image_url for post ' . get_the_ID() . ': ' . $image_url);
                                                } else {
                                                    echo '<img src="' . esc_url($placeholder_url) . '" alt="Placeholder Image" class="attachment-medium">';
                                                    error_log('Displayed placeholder image for post ' . get_the_ID() . ': ' . $placeholder_url);
                                                } 
                                            ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                <div class="entry-content">
                                    <header class="entry-header">
                                        <?php the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>'); ?>
                                    </header>    
                                    <div class="entry-summary"></div>    
                                    <div class="d-flex justify-content-start">                    
                                        <a href="<?php the_permalink(); ?>" class="btn btn-secondary">View Details</a>
                                        <?php
                                        if (is_user_logged_in() && get_post_field('post_author', get_the_ID()) == get_current_user_id()) {
                                            $nonce = wp_create_nonce('remove_collection_nonce');
                                            ?>
                                            <form method="post" class="remove-collection-form" onsubmit="return confirm('Are you sure you want to remove this issue from your collection?');">
                                                <input type="hidden" name="post_id" value="<?php echo esc_attr(get_the_ID()); ?>">
                                                <input type="hidden" name="remove_collection_nonce" value="<?php echo esc_attr($nonce); ?>">
                                                <button type="submit" class="btn btn-danger">Remove from Collection</button>
                                            </form>
                                        <?php } ?>
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
                                <p><?php esc_html_e('Add some comics to your collection!', 'collectibles'); ?></p>
                            </div>
                        </article>
                    <?php endif;    
                }
            ?> 
        </section>    
    </main>

    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>