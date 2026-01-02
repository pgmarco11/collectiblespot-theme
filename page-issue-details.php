<?php
/*
Template Name: Comic Issue Details
*/

if (!defined('ABSPATH')) {
    exit;
}

// Check for required classes
if (!class_exists('ComicRenderer') ){
    echo '<p>Error: Required classes not found. Please check if the Comic book Settings plugin is activated.</p>';
    return;
}

get_header();

// Instantiate renderer
$comic_renderer = new ComicRenderer();

// Render issue details template
$comic_renderer->render_issue_details();

get_footer();