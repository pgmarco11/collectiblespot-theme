<?php get_header(); ?>

<div class="d-flex flex-column flex-md-row w-100">

    <main class="site-main flex-fill">
        <section id="body-content" class="page-section text-center">
            <header class="page-header">                
                <h1 class="page-title"><span><?php the_title(); ?></span></h1>              
            </header>

            <?php the_content(); ?>

            <?php $extras = get_field('extras'); ?>
            <?php echo htmlspecialchars_decode($extras); ?> 
            <br>
        </section>   
         
    </main>

    <?php
    // Only load sidebar if custom field or template condition is true
    $sidebar = get_field('_show_sidebar'); 
    if ( $sidebar !== false) {
        get_sidebar();
    }
    ?>
</div>

<?php get_footer(); ?>
