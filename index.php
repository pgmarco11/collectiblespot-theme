<?php get_header(); ?>

<main class="site-main"> 
    <section id="body-content" class="body-section text-center">    
    <?php
        if (have_posts()) :
            while (have_posts()) : the_post();
                the_content();
            endwhile;
        else :
            echo '<p>No content found</p>';
        endif;
        ?>
    </section>    
</main>

<?php get_footer(); ?>
