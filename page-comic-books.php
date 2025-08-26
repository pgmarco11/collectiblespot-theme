<?php
/*
Template Name: Comic Book Titles
*/

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (!class_exists('ComicRenderer') || !class_exists('MetronAPI')) {
    echo '<p>Error: Required classes (ComicRenderer or MetronAPI) not found. Please check if the Comic Book Settings plugin is activated.</p>';
    get_footer();
    return;
}

$comic_renderer = new ComicRenderer();

$selected_publisher = isset($_GET['publisher_id']) ? intval($_GET['publisher_id']) : 0;
$page             = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$letter           = isset($_GET['letter']) && $_GET['letter'] !== '' ? sanitize_text_field($_GET['letter']) : 'all';
$per_page         = 10;

// Initialize
$initial_data = [
    'items'        => [],
    'total'        => 0,
    'type'         => $selected_publisher ? 'books' : 'publishers',
    'per_page'     => $per_page,
    'letter'       => $letter,
    'page'         => $page,
];

// Books or Publishers
if ($selected_publisher) {
    $series_data = $comic_renderer->get_series($selected_publisher, $page, $per_page, '', $letter);

    $initial_data['items']        = $series_data['items'] ?? [];
    $initial_data['total']        = $series_data['total'] ?? 0;
    $initial_data['publisher_id'] = $selected_publisher;
} else {
    $publisher_data = $comic_renderer->get_publishers('', $page, $per_page, false, $letter);

    error_log("Page $page publishers fetched: " . count($publishers_data['items'] ?? []));

    $initial_data['items'] = $publisher_data['items'] ?? [];
    $initial_data['total'] = $publisher_data['total'] ?? count($publisher_data['items']);
}

$comic_renderer->render_template($initial_data);

get_footer();