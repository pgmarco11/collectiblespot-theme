<?php
/*
Template Name: Comic Issue Details
*/

if (!defined('ABSPATH')) {
    exit;
}

// Check for required classes
if (!class_exists('ComicRenderer')) {
    echo '<p>Error: Required classes not found. Please check if the Comic book Settings plugin is activated.</p>';
    return;
}

get_header();

// Instantiate renderer
$comic_renderer = new ComicRenderer();

// Get query params safely
$title_id = isset($_GET['title_id']) ? (int) $_GET['title_id'] : 0;
$issue_id = isset($_GET['issue_id']) ? (int) $_GET['issue_id'] : 0;

if (!$title_id || !$issue_id) {
    echo '<p>Required parameters missing (series or issue ID).</p>';
    get_footer();
    return;
}

// Resolve plugin template path
$plugin_path = defined('COMICBOOKS_FETCHER_PATH')
    ? COMICBOOKS_FETCHER_PATH
    : trailingslashit(WP_PLUGIN_DIR) . 'comic-book-fetcher/';

$issue_template = $plugin_path . 'templates/issue-details-template.php';

// Safety check (recommended)
if (!file_exists($issue_template)) {
    echo '<p>Issue template not found.</p>';
    get_footer();
    return;
}

// Include template (variables above are available inside it)
include $issue_template;

get_footer();