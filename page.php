<?php 

$sidebar = get_field('_show_sidebar');
$full_width = get_field('full_width');
get_header(); ?>

<div class="d-flex flex-column flex-md-row w-100">

    <main class="site-main flex-fill">   
        <section id="body-content" class="page-section<?= $full_width ? ' px-0' : ''; ?>">
            <header class="page-header"
                <?= $full_width ? ' style="padding: 1rem 2.5rem 0 2.5rem;"' : ''; ?>
            >                
                <h1 class="page-title
                    <?= $full_width && !$sidebar ? ' text-center' : ''; ?>" 
                ><span><?php the_title(); ?></span></h1>              
            </header>

            <?php the_content(); ?>

            <?php $extras = get_field('extras'); ?>
            <?php echo htmlspecialchars_decode($extras); ?> 
            <br>
        </section>   
         
    </main>

    <?php
    // Only load sidebar if custom field or template condition is true
     
    if ( $sidebar !== false) {
        get_sidebar();
    }
    ?>
</div>

<?php get_footer(); ?>
