<?php
/*
Template Name: Comic Book Issues
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'ComicRenderer' ) ) {
    wp_die( '<p>Error: Required classes not found.</p>' );
}

/* -----------------------------------------------------------------
 *  Paths
 * ----------------------------------------------------------------- */
$plugin_path = defined( 'COMICBOOKS_FETCHER_PATH' )
    ? COMICBOOKS_FETCHER_PATH
    : trailingslashit( WP_PLUGIN_DIR ) . 'comic-book-fetcher/';

$issue_template = $plugin_path . 'templates/issue-item-template.php';
if ( ! file_exists( $issue_template ) ) {
    get_header();
    echo '<div class="container"><p class="error-message">Error: The issue template file could not be found. Please contact the site administrator.</p></div>';
    get_footer();
    return;
}

/* -----------------------------------------------------------------
 *  Input
 * ----------------------------------------------------------------- */
$title_id = isset( $_GET['title_id'] ) ? max( 0, intval( $_GET['title_id'] ) ) : 0;
$page     = isset( $_GET['page'] ) ? max( 1, intval( $_GET['page'] ) ) : 1;
$search   = isset( $_GET['search'] ) ? strtolower( trim( wp_strip_all_tags( $_GET['search'] ) ) ) : '';
$search = strtolower(trim($search));

if ( ! $title_id ) {
    wp_die( '<p>No series selected.</p>' );
}

/* -----------------------------------------------------------------
 *  Cache check for page HTML
 * ----------------------------------------------------------------- */
$cache_key = "metron:issue_list_html:{$title_id}:{$page}:{$search}";
$cached_html = get_transient( $cache_key );

if ($cached_html !== false && empty($search)) {
    get_header();
    echo $cached_html;
    ?>
    <script>
        (function(){
            'use strict';
            function hideSpinner(){
                const list = document.getElementById('issues-list');
                const spin = document.getElementById('loading-spinner');
                if(!list||!spin) return;
                if(list.querySelector('li.issue-item,.no-results')){
                    spin.classList.add('hidden');
                    list.classList.add('loaded');
                }
            }
            hideSpinner();
            window.addEventListener('pageshow',hideSpinner);
            window.addEventListener('focus',hideSpinner);
        })();
    </script>
    <?php
    get_footer();
    return;
}

/* -----------------------------------------------------------------
 *  Fetch series & issues
 * ----------------------------------------------------------------- */
$comic_renderer = new ComicRenderer();
$data           = $comic_renderer->get_series_issues( $title_id, $page, $search );

error_log("Rendering issues page | title_id=$title_id | page=$page | search='{$search}'");

error_log("get_series_issues: {$title_id}, {$page} => " . json_encode($data));

if ( isset( $data['error'] ) ) {
    get_header();
    echo '<div class="container"><p class="no-results">Error: ' . esc_html( $data['error'] ) . '</p></div>';
    get_footer();
    return;
}

/* -----------------------------------------------------------------
 *  Normalize data
 * ----------------------------------------------------------------- */
$series       = $data['series'] ?? [];
$issue_list   = $data['issue_list'] ?? [];
$all_issues   = isset($issue_list['results']) && is_array($issue_list['results']) 
                ? $issue_list['results'] 
                : [];

$total_issues = (int) ($issue_list['count'] ?? 0);
$per_page     = 10;
$total_pages  = max(1, (int) ceil($total_issues / $per_page));

// Defensive re-sort by issue number
if (!empty($all_issues)) {

    error_log("issues_found=" . count($all_issues));
    
    usort($all_issues, function($a, $b) {
        $numA = isset($a['number']) ? (float) trim((string)$a['number']) : INF;
        $numB = isset($b['number']) ? (float) trim((string)$b['number']) : INF;
        if ($numA !== $numB) {
            return $numA <=> $numB;
        }
        return ((int)($a['id'] ?? 0)) <=> ((int)($b['id'] ?? 0));
    });
}

$metron_ids = !empty($all_issues) ? array_values(array_filter(array_column($all_issues, 'id'))) : [];

/* -----------------------------------------------------------------
 *  Build CV map (async if too many issues)
 * ----------------------------------------------------------------- */
$cv_info_batch      = [];
$collection_status  = [];

if ( $metron_ids && count( $metron_ids ) <= 20 ) {
     $cv_info_batch = $comic_renderer->build_cv_map_for_series( $title_id, $page );
}

if ( is_user_logged_in() ) {
    $collection_status = $comic_renderer->get_collection_status( $metron_ids );
}

/* -----------------------------------------------------------------
 *  Render page
 * ----------------------------------------------------------------- */
get_header();

ob_start();
?>

<style>
.no-results { text-align:center; margin-top:20px; }
#issues-list { position:relative; min-height:400px; z-index:1; }
#issues-list.loaded,#issues-list.server-rendered { z-index:10; }
.issues-list { position:relative; z-index:10 !important; }
</style>

<div class="d-flex flex-column flex-md-row w-100">
    <main class="site-main flex-fill">
        <section id="body-content" class="page-section text-center">

            <!-- BREADCRUMBS & TITLE -->
            <header class="page-header">
                <nav class="category-breadcrumbs">
                    <a href="<?php echo esc_url( home_url( '/comic-catalog' ) ); ?>">Publishers</a>
                    <span class="separator">➤</span>
                    <?php if ( ! empty( $series['publisher']['id'] ) ) : ?>
                        <span class="category">
                            <a href="<?php echo esc_url( add_query_arg( 'publisher_id', $series['publisher']['id'], home_url( '/comic-catalog/' ) ) ); ?>">
                                <?php echo esc_html( $series['publisher']['name'] ?? '' ); ?>
                            </a>
                        </span>
                        <span class="separator">➤</span>
                    <?php endif; ?>
                    <span class="current-category"><?php echo esc_html( $series['name'] ?? 'Comic Series' ); ?></span>
                </nav>
                <h1 class="page-title"><span><?php echo esc_html( $series['name'] ?? 'Comic Series' ); ?></span></h1>
            </header>

            <!-- SERIES META -->
            <div class="comic-series-container">
                <ul style="display:flex;gap:.5rem;justify-content:flex-start;">
                    <li><?php echo esc_html( $series['publisher']['name'] ?? 'N/A' ); ?> / </li>
                    <li><?php echo esc_html( $series['year_began'] ?? 'N/A' ); ?> — <?php echo esc_html( $series['year_end'] ?? 'Ongoing' ); ?> / </li>
                    <li>Volume <?php echo esc_html( $series['volume'] ?? 'N/A' ); ?> / </li>
                    <li>
                    <?php 
                        $issue_count = isset($series['issue_count']) ? (int) $series['issue_count'] : 0;
                        echo esc_html(
                            $issue_count > 0
                                ? $issue_count . ' issues'
                                : 'N/A'
                        );
                        ?>
                    </li>
                </ul>
                <ul>
                    <li><strong>Series Type:</strong> <?php echo esc_html( $series['series_type']['name'] ?? 'N/A' ); ?></li>
                    <?php if ( ! empty( $series['genres'] ) && is_array( $series['genres'] ) ) : ?>
                        <li><strong>Genres:</strong> <?php echo esc_html( implode( ', ', array_column( $series['genres'], 'name' ) ) ); ?></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- SPINNER -->
            <div id="loading-spinner" class="spinner-overlay" aria-live="polite" aria-label="Loading content">
                <div class="spinner"></div>
                <p>Loading...</p>
            </div> 

            <!-- ISSUES LIST -->
            <div class="comic-issues-container">
                <div class="issues-header">
                    <h2>Issues</h2>
                    <div class="search-wrapper">
                        <input type="text" id="issue-search" value="<?php echo esc_attr( $search ); ?>" placeholder="Search issues..." aria-label="Search issues">
                    </div>
                </div>

                <div id="issues-list"
                    data-total="<?php echo esc_attr($total_issues); ?>"
                    data-page="<?php echo esc_attr($page); ?>"
                    data-title-id="<?php echo esc_attr($title_id); ?>"
                    data-metron-ids="<?php echo esc_attr(wp_json_encode($metron_ids)); ?>"
                    class="issues-list-container <?php echo (!empty($all_issues) ? 'server-rendered loaded' : ''); ?>">

                    <?php if (!empty($all_issues)) : ?>

                            <ul class="issues-list">
                                <?php foreach ($all_issues as $issue) : 
                                    if (empty($issue['id'])) continue;
                                    $metron_id = $issue['id'];
                                    $cv_issue  = $cv_info_batch[$metron_id] ?? [];
                                ?>
                                    <?php include $issue_template; ?>
                                <?php endforeach; ?>
                            </ul>

                    <?php else : ?>

                        <p class="no-results">
                            <?php if (!empty($search)) : ?>
                                No issues matching "<?php echo esc_html($search); ?>" on page <?php echo $page; ?>.
                            <?php else : ?>
                                No issues found for this series on page <?php echo $page; ?>.<br>
                                <small>(Debug: total_issues = <?php echo $total_issues; ?>, results count = <?php echo count($all_issues); ?>)</small>
                            <?php endif; ?>
                        </p>

                    <?php endif; ?>
                </div>
          
                <!-- PAGINATION -->
                <?php
                $range = 2;
                $start = max(1, $page - $range);
                $end   = min($total_pages, $page + $range);
                ?>
                <div id="pagination-wrapper">
                    <?php if ( $total_pages > 1 ) : ?>
                        <div class="pagination-wrapper">
                            <p>Page <?php echo $page; ?> of <?php echo $total_pages; ?></p>
                            <?php if ($page > 1) : ?>
                                <a href="<?php echo esc_url(add_query_arg('page', $page - 1)); ?>" class="page-btn" data-page="<?php echo $page - 1; ?>" data-title-id="<?php echo esc_attr($title_id); ?>">Previous</a>
                            <?php endif; ?>
                            <?php for ($i = $start; $i <= $end; $i++) : 
                                $is_active = $i == $page; ?>
                                <a href="<?php echo $is_active ? '#' : esc_url(add_query_arg('page', $i)); ?>"
                                   class="page-btn<?php echo $is_active ? ' active' : ''; ?>"
                                   data-page="<?php echo $i; ?>"
                                   data-title-id="<?php echo esc_attr($title_id); ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            <?php if ( $page < $total_pages ) : ?>
                                <a href="<?php echo esc_url(add_query_arg('page', $page + 1)); ?>"
                                   class="page-btn"
                                   data-page="<?php echo $page + 1; ?>"
                                   data-title-id="<?php echo esc_attr( $title_id ); ?>"
                                   data-search="<?php echo esc_attr( $search ); ?>"
                                   data-per-page="<?php echo esc_attr( $per_page ); ?>">Next</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </section>
    </main>
</div>

<?php
/* -----------------------------------------------------------------
 *  Cache rendered HTML
 * ----------------------------------------------------------------- */
$main_html = ob_get_clean();
$main_html = preg_replace_callback(
    '#(<(script|style)[^>]*>)(.*?)(</\\2>)#is',
    function ( $m ) { return $m[1] . $m[3] . $m[4]; },
    preg_replace( ['/>\s+</', '/\s+/'], ['><', ' '], $main_html )
);
set_transient( $cache_key, $main_html, 24 * HOUR_IN_SECONDS );
echo $main_html;
?>

<!-- SINGLE CV FETCH SCRIPT -->
<script>
(function(){
    'use strict';

    function hideSpinner(){
        const list = document.getElementById('issues-list');
        const spin = document.getElementById('loading-spinner');
        if(!list||!spin) return;
        if(list.querySelector('li.issue-item,.no-results')){
            spin.classList.add('hidden');
            list.classList.add('loaded');
        }
    }
    hideSpinner();
    window.addEventListener('pageshow', hideSpinner);
    window.addEventListener('focus', hideSpinner);

    const listElement = document.getElementById('issues-list');
    const metronIds = listElement ? JSON.parse(listElement.getAttribute('data-metron-ids') || '[]') : [];
    const nonce = '<?php echo wp_create_nonce( 'comicbooks_fetchers_data' ); ?>';

    if (!metronIds.length) return;

    // Only fetch if more than 10 issues or CV info not preloaded
    setTimeout(() => {
        fetch(ajaxurl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'load_comic_vine_batch',
                nonce: nonce,
                metron_ids: metronIds.join(',')
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                console.log('CV data loaded:', data.data.cv_data);
            }
        });
    }, 500);
    
})();
</script>

<?php get_footer(); ?>