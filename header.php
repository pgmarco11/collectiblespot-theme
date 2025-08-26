<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="<?php the_title(); ?>">
    <meta property="og:description" content="<?php bloginfo( 'description' ); ?>">
   
    <meta property="og:url" content="<?php echo esc_url( home_url('/' ) ); ?>">
    <meta property="og:type" content="website">

    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header>
    <?php get_template_part( 'includes/templates/header-section' ); ?>
</header>
