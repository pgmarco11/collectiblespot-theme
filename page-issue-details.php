<?php
/*
Template Name: Comic Issue Details
*/

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Check for required classes
if (!class_exists('ComicRenderer') || !class_exists('MetronAPI')) {
    echo '<p>Error: Required classes (ComicRenderer or MetronAPI) not found. Please check if the Comic book Settings plugin is activated.</p>';
    return;
}

// Instantiate renderer
$comic_renderer = new ComicRenderer();

// Render issue details template
$comic_renderer->render_issue_details();

get_footer();