<?php get_header(); ?>

<main class="site-main">
    <section id="hero" class="hero-section text-center"> 
            <div class="container"> 
                <div class="hero-content">
                    <?php $hero_section = get_field('hero_section'); ?>
                    <?php echo htmlspecialchars_decode($hero_section); ?> 
                </div>
            </div>
    </section> 
    <?php
    if (have_posts()) :

    while (have_posts()) : the_post();

    ?> 
    <section id="body-content" class="body-section">    
    <?php  

        the_content();

        endwhile;
            
        else :

        echo '<p>No content found</p>';           

    ?>
    </section>

    <?php endif; ?>
</main>

<?php get_footer(); ?>
