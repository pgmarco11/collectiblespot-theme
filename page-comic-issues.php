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
$plugin_path    = defined( 'COMICBOOKS_FETCHER_PATH' )
    ? COMICBOOKS_FETCHER_PATH
    : trailingslashit( WP_PLUGIN_DIR ) . 'comic-book-fetcher/';

$issue_template = $plugin_path . 'templates/issue-item-template.php';

if ( ! file_exists( $issue_template ) ) {
    wp_die( '<p>Error: Issue template not found.</p>' );
}

/* -----------------------------------------------------------------
 *  Input
 * ----------------------------------------------------------------- */
$title_id   = isset( $_GET['title_id'] ) ? max( 0, intval( $_GET['title_id'] ) ) : 0;
$page       = isset( $_GET['page'] ) ? max( 1, intval( $_GET['page'] ) ) : 1;
$search     = isset( $_GET['search'] ) ? strtolower( trim( wp_strip_all_tags( $_GET['search'] ) ) ) : '';

if ( ! $title_id ) {
    wp_die( '<p>No series selected.</p>' );
}

/* -----------------------------------------------------------------
 *  Page-level cache
 * ----------------------------------------------------------------- */
$cache_key   = "metron:issue_list:{$title_id}:{$page}:" . md5( $search );
$cached_html = get_transient( $cache_key );

if ( $cached_html !== false ) {
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
 *  Data
 * ----------------------------------------------------------------- */
$comic_renderer = new ComicRenderer();
$data           = $comic_renderer->get_series_issues( $title_id, $page, $search );

if ( isset( $data['error'] ) ) {
    get_header();
    echo '<div class="container"><p class="no-results">Error: ' . esc_html( $data['error'] ) . '</p></div>';
    get_footer();
    return;
}

/* -----------------------------------------------------------------
 *  Normalize data
 * ----------------------------------------------------------------- */
$series          = $data['series'] ?? [];
$issue_list_data = $data['issue_list'] ?? [];
$all_issues      = $issue_list_data['results'] ?? [];
$total_issues    = (int) ( $issue_list_data['count'] ?? 0 );
$per_page        = 10;
$total_pages     = max( 1, (int) ceil( $total_issues / $per_page ) );

$metron_ids      = array_filter( array_column( $all_issues, 'id' ) );
$cv_info_batch   = [];
$collection_status = [];

if ( $metron_ids ) {
    $cv_info_batch = $comic_renderer->get_comicvine_issue_info_batch( $metron_ids );

    if ( is_user_logged_in() ) {
        $collection_status = $comic_renderer->get_collection_status( $metron_ids );
    }
}

/* -----------------------------------------------------------------
 *  Output header first
 * ----------------------------------------------------------------- */
get_header();

/* -----------------------------------------------------------------
 *  Capture main content
 * ----------------------------------------------------------------- */
ob_start();
?>

<style>
.no-results { text-align:center; margin-top:20px; }
#loading-spinner.hidden { opacity:0 !important; visibility:hidden !important; z-index:-1 !important; }
#loading-spinner p { margin:0; font-size:16px; }
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
                    <li><?php echo esc_html( $series['issue_count'] ? $series['issue_count'] . ' issues' : 'N/A' ); ?></li>
                </ul>
                <ul>
                    <li><strong>Series Type:</strong> <?php echo esc_html( $series['series_type']['name'] ?? 'N/A' ); ?></li>
                    <?php if ( ! empty( $series['genres'] ) && is_array( $series['genres'] ) ) : ?>
                        <li><strong>Genres:</strong> <?php echo esc_html( implode( ', ', array_column( $series['genres'], 'name' ) ) ); ?></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- ISSUES LIST -->
            <div class="comic-issues-container">
                <div class="issues-header">
                    <h2>Issues</h2>
                    <div class="search-wrapper">
                        <input type="text" id="issue-search" value="<?php echo esc_attr( $search ); ?>" placeholder="Search issues..." aria-label="Search issues">
                    </div>
                </div>

                <!-- SPINNER -->
                <div id="loading-spinner"
                    class="spinner-overlay <?php echo ( $page === 1 && empty( $search ) && $all_issues ) ? 'hidden' : ''; ?>"
                     aria-busy="true" aria-label="Loading content">
                    <div class="spinner"></div>
                    <p>Loading series issues...</p>  
                </div>

                <!-- LIST -->
                <div id="issues-list"
                     data-total="<?php echo $total_issues; ?>"
                     data-page="<?php echo $page; ?>"
                     class="<?php echo ( $page === 1 && empty( $search ) && $all_issues ) ? 'server-rendered loaded' : ''; ?>">

                    <?php if ( $page === 1 && empty( $search ) ) : ?>

                        <?php error_log('all_issues count: ' . count($all_issues)); ?>

                        <?php
                        // Fallback sort         
                        usort($all_issues, function($a, $b) {
                                    $idA = (int) ($a['id'] ?? 0);
                                    $idB = (int) ($b['id'] ?? 0);
                                    return $idA <=> $idB;
                        });
                    
                        ?>
                        <?php if ( $all_issues ) : ?>
                            <ul class="issues-list">
                                <?php foreach ( $all_issues as $issue ) : ?>
                                    <?php
                                    if ( empty( $issue['id'] ) ) continue;
                                    $issue_cv_info = $cv_info_batch[ $issue['id'] ] ?? [];
                                    $issue_collection = $collection_status[ $issue['id'] ] ?? false;
                                    $issue_highlights = $cv_info_batch[ $issue['id'] ]['_highlights'] ?? [];
                                    ?>
                                    <?php include $issue_template; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php else : ?>
                            <p class="no-results">No issues found for this series.</p>
                        <?php endif; ?>

                    <?php else : ?>
                        <!-- AJAX placeholders -->
                        <?php for ( $i = 0; $i < min( 10, $total_issues ); $i++ ) : ?>
                            <div class="issue-placeholder"></div>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>

                <!-- PAGINATION -->  
                <?php
                $page = (int) $page;
                $total_pages = (int) $total_pages;
                ?>

                <div id="pagination-wrapper">
                <?php if ( $total_pages > 1 ) : ?>
                    <div class="pagination-wrapper">
                        <p>Page <?php echo $page; ?> of <?php echo $total_pages; ?></p>

                        <?php if ( $page > 1 ) : ?>
                            <button type="button"
                                class="page-btn"
                                data-page="<?php echo $page - 1; ?>"
                                data-title-id="<?php echo esc_attr( $title_id ); ?>"
                                data-search="<?php echo esc_attr( $search ); ?>"
                                data-per-page="<?php echo esc_attr( $per_page ); ?>">
                                Previous
                            </button>
                        <?php endif; ?>

                        <?php
                        $start = max( 1, $page - 2 );
                        $end   = min( $total_pages, $page + 2 );

                        for ( $i = $start; $i <= $end; $i++ ) :
                        ?>
                            <button type="button"
                                class="page-btn<?php echo $i == $page ? ' active' : ''; ?>"
                                data-page="<?php echo $i; ?>"
                                data-title-id="<?php echo esc_attr( $title_id ); ?>"
                                data-search="<?php echo esc_attr( $search ); ?>"
                                data-per-page="<?php echo esc_attr( $per_page ); ?>">
                                <?php echo $i; ?>
                            </button>
                        <?php endfor; ?>

                        <?php if ( $page < $total_pages ) : ?>
                            <button type="button"
                                class="page-btn"
                                data-page="<?php echo $page + 1; ?>"
                                data-title-id="<?php echo esc_attr( $title_id ); ?>"
                                data-search="<?php echo esc_attr( $search ); ?>"
                                data-per-page="<?php echo esc_attr( $per_page ); ?>">
                                Next
                            </button>
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
 *  Cache the rendered HTML
 * ----------------------------------------------------------------- */
$main_html = ob_get_clean();

$main_html = preg_replace_callback(
    '#(<(script|style)[^>]*>)(.*?)(</\\2>)#is',
    function ( $m ) {
        return $m[1] . $m[3] . $m[4];
    },
    preg_replace( ['/>\s+</', '/\s+/'], ['><', ' '], $main_html )
);

set_transient( $cache_key, $main_html, 2 * HOUR_IN_SECONDS );

echo $main_html;
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

<?php get_footer(); ?>