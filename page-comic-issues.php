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
$page = absint(
    get_query_var(
        'catalog_page',
        1
    )
);

if ($page < 1) {
    $page = 1;
}
$search   = isset( $_GET['search'] ) ? strtolower( trim( wp_strip_all_tags( $_GET['search'] ) ) ) : '';

if ( ! $title_id ) {
    wp_die( '<p>No series selected.</p>' );
}

$owned_issues = function_exists('tcs_inventory_repository')
    ? tcs_inventory_repository()->owned_series_records($title_id)
    : [];

$requested_view = $_GET['series_view'] ?? '';
$show_my_issues = is_user_logged_in()
    && is_string($requested_view)
    && wp_unslash($requested_view) === 'owned';

$issues_args = ['title_id' => $title_id];

if ($page > 1) {
    $issues_args['page'] = $page;
}

if ($search !== '') {
    $issues_args['search'] = $search;
}

$issues_url = add_query_arg(
    $issues_args,
    home_url('/comic-database/issues/')
);

$my_issues_url = add_query_arg('series_view', 'owned', $issues_url);

/* -----------------------------------------------------------------
 *  Fetch series & issues
 * ----------------------------------------------------------------- */
$comic_renderer = new ComicRenderer();

if ($show_my_issues) {
    $cached_series = comicbooks_read_series_header($title_id);

    // Refresh expired or incomplete details without blocking My Issues.
    comicbooks_queue_series_metadata($title_id);

    $first_owned = $owned_issues[0] ?? [];

    $series = array_replace([
        'id'         => $title_id,
        'name'       => $first_owned['title'] ?? 'Comic Series',
        'volume'     => ($first_owned['volume'] ?? '') ?: 'N/A',
        'year_began' => 'N/A',
        'year_end'   => 'N/A',
        'publisher'  => [
            'name' => $first_owned['publisher'] ?? 'N/A',
        ],
    ], $cached_series);

    $data = [
        'series'     => $series,
        'issue_list' => [
            'results' => [],
            'count'   => 0,
        ],
    ];
} else {
    $data = $comic_renderer->get_series_issues(
        $title_id,
        $page,
        $search
    );
}


if (isset($data['error'])) {
    $temporary = !empty($data['temporary_error']);

    $retry_after = max(
        1,
        (int) ($data['retry_after'] ?? 2)
    );

    $retry_count = isset($_GET['metron_retry'])
        ? absint(wp_unslash($_GET['metron_retry']))
        : 0;

    // Automatic retries stop after two page reloads.
    $can_retry = $temporary && $retry_count < 2;

    $retry_url = add_query_arg(
        [
            'title_id' => $title_id,
            'page' => $page,
            'search' => $search,
        ],
        home_url('/comic-database/issues/')
    );

    if (
        is_string($requested_view)
        && wp_unslash($requested_view) === 'owned'
    ) {
        $retry_url = add_query_arg(
            'series_view',
            'owned',
            $retry_url
        );
    
        $requested_owned_page = $_GET['owned_page'] ?? '';
    
        if (
            is_string($requested_owned_page)
            && ctype_digit($requested_owned_page)
        ) {
            $retry_url = add_query_arg(
                'owned_page',
                max(1, (int) $requested_owned_page),
                $retry_url
            );
        }
    }

    $automatic_retry_url = add_query_arg(
        'metron_retry',
        $retry_count + 1,
        $retry_url
    );

    if ($temporary) {
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }

        status_header(503);
        nocache_headers();
        header('Retry-After: ' . $retry_after);
    }

    get_header();
    ?>

    <div class="container" id="metron-retry-message">
        <p class="no-results" role="status">
            <?php
            if ($can_retry) {
                echo esc_html(
                    'The comic service is temporarily busy. Retrying shortly…'
                );
            } elseif ($temporary) {
                echo esc_html(
                    'The comic service is still busy. Please try again shortly.'
                );
            } else {
                echo esc_html('Error: ' . $data['error']);
            }
            ?>
        </p>

        <p>
            <a href="<?php echo esc_url($retry_url); ?>">
                Try again
            </a>
        </p>
    </div>

    <?php if ($can_retry) : ?>
        <script>
        window.setTimeout(function () {
            window.location.replace(
                <?php
                echo wp_json_encode(
                    $automatic_retry_url,
                    JSON_HEX_TAG |
                    JSON_HEX_AMP |
                    JSON_HEX_APOS |
                    JSON_HEX_QUOT
                );
                ?>
            );
        }, <?php
            echo (int) min(
                2147483647,
                $retry_after * 1000
            );
        ?>);
        </script>
    <?php endif; ?>

    <?php
    get_footer();
    return;
}

/* -----------------------------------------------------------------
 *  Normalize data
 * ----------------------------------------------------------------- */
$series             = $data['series'] ?? [];
$issue_list         = $data['issue_list'] ?? [];
$all_issues         = isset($issue_list['results']) && is_array($issue_list['results'])
    ? $issue_list['results']
    : [];

$total_issues       = (int) ($issue_list['count'] ?? 0);
$metron_ids         = [];
$collection_status  = [];
$total_pages = (int) ($data['total_pages'] ?? 1);

/*
 * Build Comic Vine info for server-rendered issues.
 */
$client       = new MetronClient();
$data_service = new ComicDataService($client);

/*
 * Only request Comic Vine information when Metron does not already
 * provide an issue cover.
 */
$issues_needing_cv = array_values(
    array_filter(
        $all_issues,
        static function ($issue) {
            return empty($issue['image']);
        }
    )
);

$cv_info_batch = !empty($issues_needing_cv)
    ? $data_service->get_cv_info_batch($issues_needing_cv)
    : [];

// Defensive re-sort by issue number
if (!empty($all_issues)) {
        
    usort($all_issues, function($a, $b) {
        $numA = isset($a['number']) ? (float) trim((string)$a['number']) : INF;
        $numB = isset($b['number']) ? (float) trim((string)$b['number']) : INF;
        if ($numA !== $numB) {
            return $numA <=> $numB;
        }
        return ((int)($a['id'] ?? 0)) <=> ((int)($b['id'] ?? 0));
    });

    $metron_ids = array_values(array_filter(array_column($all_issues, 'id')));

    if ( is_user_logged_in() ) {
        $collection_status = $comic_renderer->get_collection_status( $metron_ids );
    }
    
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
                    <a href="<?php echo esc_url( home_url( '/comic-database' ) ); ?>">Publishers</a>
                    <span class="separator">➤</span>
                    <?php if ( ! empty( $series['publisher']['id'] ) ) : ?>
                        <span class="category">
                            <a href="<?php echo esc_url( add_query_arg( 'publisher_id', $series['publisher']['id'], home_url( '/comic-database/' ) ) ); ?>">
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
            <div class="comic-series-container" id="tcs-series-header">
                <?php
                if (
                    !$show_my_issues
                    || comicbooks_valid_series_header($series, $title_id)
                ) :
                    ?>
                    <?php echo comicbooks_series_header_html($series); ?>
                <?php else : ?>
                    <p role="status">Loading series details…</p>
                <?php endif; ?>
            </div>

            <?php
            if (
                $show_my_issues
                && !comicbooks_valid_series_header($series, $title_id)
            ) :
                ?>
                <script>
                (() => {
                    const root = document.getElementById('tcs-series-header');

                    if (!root) return;

                    const url = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
                    const seriesId = <?php echo wp_json_encode((string) $title_id); ?>;
                    const nonce = <?php echo wp_json_encode(wp_create_nonce('comicbooks_fetchers_data')); ?>;

                    const delay = ms => new Promise(
                        resolve => setTimeout(resolve, ms)
                    );

                    async function poll() {
                        root.innerHTML =
                            '<p role="status">Loading series details…</p>';

                        for (let attempt = 0; attempt < 20; attempt++) {
                            await delay(3000);

                            if (!root.isConnected) return;

                            const controller = new AbortController();

                            const timeout = setTimeout(
                                () => controller.abort(),
                                5000
                            );

                            try {
                                const response = await fetch(url, {
                                    method: 'POST',
                                    credentials: 'same-origin',
                                    signal: controller.signal,
                                    body: new URLSearchParams({
                                        action: 'tcs_read_series_header',
                                        series_id: seriesId,
                                        nonce
                                    })
                                });

                                if (
                                    response.status === 403
                                    || response.status === 401
                                ) {
                                    root.textContent =
                                        'Your session expired. Reload to load series details.';
                                    return;
                                }

                                if (!response.ok) continue;

                                const result = await response.json();

                                if (
                                    result.success
                                    && result.data.pending === false
                                ) {
                                    root.innerHTML = result.data.html;
                                    return;
                                }
                            } catch {
                                // Retry a temporary connection failure.
                            } finally {
                                clearTimeout(timeout);
                            }
                        }

                        root.textContent =
                            'Series details are temporarily unavailable. ';

                        const retry = document.createElement('button');

                        retry.type = 'button';
                        retry.className = 'button';
                        retry.textContent = 'Try again';
                        retry.addEventListener('click', poll, { once: true });

                        root.append(retry);
                    }

                    poll();
                })();
                </script>
            <?php endif; ?>

            <!-- ISSUES LIST -->
            <div class="comic-issues-container">
            <?php if ($owned_issues || $show_my_issues) : ?>
                    <nav class="nav nav-tabs mb-3" aria-label="Series views">
                        <a
                            class="nav-link<?php echo $show_my_issues ? '' : ' active'; ?>"
                            href="<?php echo esc_url($issues_url); ?>"
                            <?php if (!$show_my_issues) : ?>aria-current="page"<?php endif; ?>
                        >
                            Issues
                        </a>
                        <a
                            class="nav-link<?php echo $show_my_issues ? ' active' : ''; ?>"
                            href="<?php echo esc_url($my_issues_url); ?>"
                            data-owned-view-param="series_view"
                            <?php if ($show_my_issues) : ?>aria-current="page"<?php endif; ?>
                        >
                            My Issues
                        </a>
                    </nav>
                <?php 
                    endif;
                    
                    if ($show_my_issues) :
                        
                        $owned_records = $owned_issues;
                        $owned_template = 'catalog-owned-issues.php';
                        
                        if ($owned_records) {
                            require __DIR__ . '/parts/catalog-owned-page.php';
                        } else {
                            echo '<div id="catalog-owned-issues">'
                                . '<p class="no-results">'
                                . 'You have no issues from this series in your collection.'
                                . '</p></div>';
                        }
                        
                ?>
                <?php else : ?>
                    <!-- SPINNER -->
                    <div id="loading-spinner" class="spinner-overlay" aria-live="polite" aria-label="Loading content">
                        <div class="spinner"></div>
                        <p>Loading...</p>
                    </div> 

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
                        class="issues-list-container <?php echo (!empty($all_issues) ? 'server-rendered loaded' : ''); ?>">

                        <?php if (!empty($all_issues)) : ?>

                                <ul class="issues-list">
                                <?php foreach ($all_issues as $issue) :

                                    if (empty($issue['id'])) {
                                        continue;
                                    }

                                    $metron_id = (int) $issue['id'];

                                    $cv_issue = $cv_info_batch[$metron_id] ?? [];

                                    include $issue_template;

                                    endforeach; ?>
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
                    <div id="pagination-wrapper" class="pagination-wrapper">
                        <?php echo comicbooks_issue_pagination_html(
                            $title_id, $page, $total_pages, $search
                        ); ?>
                    </div>         
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>

<?php
/* -----------------------------------------------------------------
 *  Output rendered HTML
 * ----------------------------------------------------------------- */
echo ob_get_clean();
?>

<?php get_footer(); ?>