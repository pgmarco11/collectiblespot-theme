<aside class="sidebar">
    <?php
     if ( is_category() ): 

        if (is_active_sidebar('category-sidebar')) {          
            dynamic_sidebar('category-sidebar');            
        } else {
            echo '<p>Add widgets to the "Category Sidebar" from your WordPress admin.</p>';
        }
    
    elseif ( is_active_sidebar( 'primary-sidebar' ) ) :
    
            dynamic_sidebar( 'primary-sidebar' ); ?>

    <?php else : ?>

        <p>Add widgets to the "Primary Sidebar" from your WordPress admin.</p>

    <?php endif; ?>
</aside>