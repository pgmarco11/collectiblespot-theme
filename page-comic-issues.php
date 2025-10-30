<?php
/*
Template Name: Comic Book Issues
*/
if (!defined('ABSPATH')) exit;

if (!class_exists('ComicRenderer')) {
    wp_die('<p>Error: Required classes not found.</p>');
}

$plugin_path = defined('COMICBOOKS_FETCHER_PATH') 
    ? COMICBOOKS_FETCHER_PATH 
    : trailingslashit(WP_PLUGIN_DIR) . 'comic-book-fetcher/';
$issue_template = $plugin_path . 'templates/issue-item-template.php';

if (!file_exists($issue_template)) {
    wp_die('<p>Error: Issue template not found.</p>');
}

$title_id = isset($_GET['title_id']) ? intval($_GET['title_id']) : 0;
$issue_page = isset($_GET['issue_page']) ? max(1, intval($_GET['issue_page'])) : 1;
$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

$cache_key = "metron:issues_page:{$title_id}:{$issue_page}:" . md5($search);
$cached_html = get_transient($cache_key);

if ($cached_html !== false) {
    get_header();
    echo $cached_html;
    ?>
    <script>
    (function() {
        'use strict';
        function hideSpinnerIfLoaded() {
            const issuesList = document.getElementById('issues-list');
            const loadingSpinner = document.getElementById('loading-spinner');
            if (!issuesList || !loadingSpinner) return false;
            const hasContent = issuesList.querySelector('li.issue-item') || issuesList.querySelector('.no-results');
            if (hasContent) {
                loadingSpinner.classList.add('hidden');
                issuesList.classList.add('loaded');
                console.log('Spinner hidden instantly');
                return true;
            }
            return false;
        }
        if (hideSpinnerIfLoaded()) return;
        window.addEventListener('pageshow', hideSpinnerIfLoaded);
        window.addEventListener('focus', hideSpinnerIfLoaded);
    })();
    </script>
    <?php
    get_footer();
    return;
}

$comic_renderer = new ComicRenderer();
$data = $comic_renderer->get_series_issues($title_id, $issue_page, $search);

if (isset($data['error'])) {
    get_header();
    echo '<div class="container"><p class="no-results">Error: ' . esc_html($data['error']) . '</p></div>';
    get_footer();
    return;
}

$series = $data['series'];
$issue_list_data = $data['issue_list'] ?? [];
$all_issues = $issue_list_data['results'] ?? [];
$total_issues = $issue_list_data['count'] ?? 0;
$per_page = 10;
$total_pages = ceil($total_issues / $per_page);

$metron_ids = array_column($all_issues, 'id') ?? [];
$cv_info_batch = [];
$collection_status = [];

if (!empty($metron_ids)) {
    $cv_info_batch = $comic_renderer->get_comicvine_issue_info_batch($metron_ids);
    if (is_user_logged_in()) {
        $collection_status = $comic_renderer->get_collection_status($metron_ids);
    }
}

// OUTPUT HEADER FIRST
get_header();

// START CAPTURING MAIN CONTENT
ob_start();
?>

<!-- **ORIGINAL HTML/CSS STRUCTURE - UNCHANGED** -->
<style>
.no-results {
  text-align: center;
  margin-top: 20px;
}
#loading-spinner.hidden {
    opacity: 0 !important;
    visibility: hidden !important;
    z-index: -1 !important;
}
#loading-spinner p {
  margin: 0;
  font-size: 16px;
}
#issues-list {
    position: relative;
    min-height: 400px;
    z-index: 1;
}
#issues-list.loaded,
#issues-list.server-rendered {
    z-index: 10;
}
.issues-list {
    position: relative;
    z-index: 10 !important;
}
</style>

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
                
                <!-- ✅ SPINNER ALWAYS RENDERED - Smart JS hides it -->
                <div id="loading-spinner" class="<?php echo ($issue_page === 1 && empty($search) && !empty($all_issues)) ? 'hidden' : ''; ?>" aria-busy="true" aria-label="Loading content">
                    <div class="spinner"></div>
                    <p>Loading issues...</p>
                </div>
                
                <div id="issues-list" 
                    data-total="<?php echo $total_issues; ?>" 
                    data-page="<?php echo $issue_page; ?>" 
                    class="<?php echo ($issue_page === 1 && empty($search) && !empty($all_issues)) ? 'server-rendered loaded' : ''; ?>">
                    
                    <?php if ($issue_page === 1 && empty($search)): ?>
                        <!-- Server-side content -->
                        <?php if (!empty($all_issues) && is_array($all_issues)): ?>
                            <ul class="issues-list">
                                <?php foreach ($all_issues as $index => $issue): ?>
                                    <?php if (isset($issue['id'])): include $issue_template; endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="no-results">No issues found for this series.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- AJAX placeholders -->
                        <?php for ($i = 0; $i < min(10, $total_issues); $i++): ?>
                            <div class="issue-placeholder"></div>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination unchanged -->
                <div id="pagination-wrapper">
                    <?php
                        if ($total_pages > 1) {
                            echo '<div class="pagination-wrapper">';
                            echo '<p>Page ' . $issue_page . ' of ' . $total_pages . '</p>';
                            if ($issue_page > 1) {
                                echo '<button type="button" class="page-btn" data-page="' . ($issue_page - 1) . '" data-title-id="' . $title_id . '" data-search="' . esc_attr($search) . '">Previous</button>';
                            }
                            for ($i = max(1, $issue_page - 2); $i <= min($total_pages, $issue_page + 2); $i++) {
                                echo '<button type="button" class="page-btn' . ($i === $issue_page ? 'active' : '') . '" data-page="' . $i . '" data-title-id="' . $title_id . '" data-search="' . esc_attr($search) . '">' . $i . '</button>';
                            }
                            if ($issue_page < $total_pages) {
                                echo '<button type="button" class="page-btn" data-page="' . ($issue_page + 1) . '" data-title-id="' . $title_id . '" data-search="' . esc_attr($search) . '">Next</button>';
                            }
                            echo '</div>';
                         }
                    ?>
                </div>
            </div>
        </section>
    </main>
</div>

<?php
// CAPTURE AND CACHE
$main_html = ob_get_clean();

function minify_html_safe($html) {
    return preg_replace_callback(
        '#(<(script|style)[^>]*>)(.*?)(</\\2>)#is',
        function($m) { return $m[1] . $m[3] . $m[4]; },
        preg_replace(['/>\s+</', '/\s+/'], ['><', ' '], $html)
    );
}

$main_html = minify_html_safe($main_html);
set_transient($cache_key, $main_html, 2 * HOUR_IN_SECONDS);

echo $main_html;
?>

<!-- NOW ADD SCRIPT SAFELY AFTER ECHO -->
<script>
(function() {
    'use strict';
    function hideSpinnerIfLoaded() {
        const issuesList = document.getElementById('issues-list');
        const loadingSpinner = document.getElementById('loading-spinner');
        if (!issuesList || !loadingSpinner) return false;
        const hasContent = issuesList.querySelector('li.issue-item') || issuesList.querySelector('.no-results');
        if (hasContent) {
            loadingSpinner.classList.add('hidden');
            issuesList.classList.add('loaded');
            console.log('Spinner hidden instantly');
            return true;
        }
        return false;
    }
    if (hideSpinnerIfLoaded()) return;
    window.addEventListener('pageshow', hideSpinnerIfLoaded);
    window.addEventListener('focus', hideSpinnerIfLoaded);
})();
</script>

<?php get_footer(); ?>