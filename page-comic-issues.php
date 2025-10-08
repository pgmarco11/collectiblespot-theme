<?php
/*
Template Name: Comic Book Issues
*/
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('ComicRenderer')) {
    error_log('ComicRenderer class not found');
    wp_die('<p>Error: Required classes (ComicRenderer) not found.</p>');
}

$title_id = isset($_GET['title_id']) ? intval($_GET['title_id']) : 0;
$issue_page = isset($_GET['issue_page']) ? max(1, intval($_GET['issue_page'])) : 1;
$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

error_log("Comic Book Issues Template: title_id=$title_id, issue_page=$issue_page, search=$search");

get_header();

if (!$title_id) {
    echo '<div class="container"><p>No series selected. Please go back and select a series.</p></div>';
    get_footer();
    return;
}

$comic_renderer = new ComicRenderer();
$data = $comic_renderer->get_series_issues($title_id, $issue_page, $search);

if (isset($data['error'])) {
    error_log("Error fetching series data: {$data['error']}");
    echo '<div class="container"><p>' . esc_html($data['error']) . '</p></div>';
    get_footer();
    return;
}

$series = $data['series'];
?>
<div class="d-flex flex-column flex-md-row w-100">
    <main class="site-main flex-fill">
        <section id="body-content" class="page-section text-center">
            <header class="page-header">
                <nav class="category-breadcrumbs">
                    <a href="<?php echo esc_url(home_url('/comic-books')); ?>">Publishers</a>
                    <span class="separator">&#10148;</span>
                    <span class="category"><a href="<?php echo esc_url(home_url('/comic-books/?publisher_id=' . $series['publisher']['id'])); ?>">
                        <?php echo esc_html($series['publisher']['name']); ?>
                    </a></span>
                    <span class="separator">&#10148;</span>
                    <span class="current-category"><?php echo esc_html($series['name'] ?? 'Comic Series'); ?></span>
                </nav>
                <h1 class="page-title"><span><?php echo esc_html($series['name'] ?? 'Comic Series'); ?></span></h1>
            </header>
            <div class="comic-series-container">
                <ul style="display: flex; gap: .5rem; justify-content: flex-start;">
                    <li><?php echo esc_html($series['publisher']['name'] ?? 'N/A'); ?> &nbsp;/ </li>
                    <li><?php echo esc_html($series['year_began'] ?? 'N/A'); ?> &mdash; <?php echo esc_html($series['year_end'] ?? 'Ongoing'); ?> &nbsp;/ </li>
                    <li>Volume <?php echo esc_html($series['volume'] ?? 'N/A'); ?> &nbsp;/ </li>
                    <li><?php echo esc_html($series['issue_count'] ? $series['issue_count'] . ' issues' : 'N/A'); ?> </li>
                </ul>
                <ul>
                    <li><strong>Series Type:</strong> <?php echo esc_html($series['series_type']['name'] ?? 'N/A'); ?></li>
                    <?php if (!empty($series['genres']) && is_array($series['genres'])): ?>
                        <li><strong>Genres:</strong> <?php echo esc_html(implode(', ', array_column($series['genres'], 'name'))); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="comic-issues-container">
                <div class="issues-header">
                    <h2>Issues</h2>
                    <div class="search-wrapper">
                        <input type="text" id="issue-search" value="<?php echo esc_attr($search); ?>" placeholder="Search issues..." aria-label="Search issues">
                    </div>
                </div>
                <div id="issues-list" style="min-height: 400px;">
                    <div id="loading-spinner" style="display:none;" aria-busy="true" aria-label="Loading content">
                        <div class="spinner"></div>
                        <p>Loading issues...</p>
                    </div>
                </div>
                <div id="pagination-wrapper"></div>
            </div>
        </section>
    </main>
</div>
<?php get_footer(); ?>