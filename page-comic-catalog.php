<?php
/*
Template Name: Comic Catalog
*/
if (current_user_can('manage_options')): ?>
    <script>
    // Only admins see this live debug panel
    window.PHP_DEBUG = <?= json_encode([
        'url'           => $_SERVER['REQUEST_URI'],
        'page'          => $page,
        'letter'        => $letter,
        'search'        => $search,
        'publisher_id'  => $selected_publisher,
        'initial_data'  => $initial_data,           // This is the MOST IMPORTANT one
        'server_time'   => date('H:i:s'),
    ]) ?>;
    console.log('%c PHP SERVER DEBUG → ', 'background:#000;color:#0f0;font-size:14px', window.PHP_DEBUG);
    </script>
    <?php endif; 

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'ComicRenderer' ) ) {
    wp_die( '<p>Error: ComicRenderer class not found. Please activate the plugin.</p>' );
}

/* -----------------------------------------------------------------
 *  Input
 * ----------------------------------------------------------------- */

$per_page = 10;
$page   = max(1, get_query_var('page', 1));
$letter = sanitize_text_field(get_query_var('letter', 'all'));
$search = sanitize_text_field(get_query_var('search', ''));
$selected_publisher = intval(get_query_var('publisher_id', 0));

/* -----------------------------------------------------------------
 *  Renderer
 * ----------------------------------------------------------------- */
$comic_renderer = new ComicRenderer();

/* -----------------------------------------------------------------
 *  Initial data
 * ----------------------------------------------------------------- */
    $initial_data = [
        'items'        => [],
        'total'        => 0,
        'type'         => 'publishers',
        'per_page'     => $per_page,
        'page'         => $page,
        'letter'       => $letter ?: 'all',
        'publisher_id' => $selected_publisher,
        'search'       => $search,
    ];
/* -----------------------------------------------------------------
 *  Fetch data
 * ----------------------------------------------------------------- */
if ( $selected_publisher > 0 ) {
    $series_data = $comic_renderer->get_series(
        $selected_publisher,
        $page,
        $per_page,
        $search,
        $letter,
        true   
    );
    $initial_data['items']         = $series_data['items']   ?? [];
    $initial_data['total']         = $series_data['total']   ?? 0;
    $initial_data['type']          = 'series';
    $initial_data['per_page']      = $series_data['per_page'] ?? $per_page;

    $publisher_info = $comic_renderer->get_publisher_info($selected_publisher);

} elseif ( empty($search) ) {

    $letter = $letter ?: 'all';
    $bypass_cache = ($page > 1);

    $pub_data = $comic_renderer->get_enriched_publishers(
        $page,
        10,
        $letter,
        $bypass_cache
    );

    $initial_data['items']     = $pub_data['items'] ?? [];
    $initial_data['total']     = $pub_data['total'] ?? 0;
    $initial_data['type']     = 'publishers';
    $initial_data['per_page']  = 10;
    
}

// Dropdown – CORRECT 4-PARAM CALL
$dropdown_publishers = $comic_renderer->get_publishers( '', 1, 1000, 'all' )['items'] ?? [];

/* -----------------------------------------------------------------
 *  Output
 * ----------------------------------------------------------------- */
get_header();
?>

<div class="d-flex flex-column flex-md-row w-100">
    <main class="site-main flex-fill">
        <section id="body-content" class="page-section text-center">

            <!-- BREADCRUMBS -->
            <header class="page-header">
                <nav class="category-breadcrumbs">
                <a href="<?php echo esc_url(get_permalink()); ?>">Publishers</a>
                    <?php if ( ! empty( $publisher_info['name'] ?? '' ) ) : ?>
                        <span class="separator">➤</span>
                        <span class="current-category"><?php echo esc_html( $publisher_info['name'] ); ?></span>
                    <?php endif; ?>
                </nav>
                <h1 class="page-title"><span><?php the_title(); ?></span></h1>
            </header>

            <!-- FILTERS -->
            <div class="page-filters">
                <select name="publisher_id" id="publisher-select" aria-label="Select Publisher">
                    <option value="">Select a publisher</option>
                    <?php foreach ( $dropdown_publishers as $pub ) : ?>
                        <?php if ( empty( $pub['id'] ) || empty( $pub['name'] ) ) continue; ?>
                        <option value="<?php echo esc_attr( $pub['id'] ); ?>" <?php selected( $selected_publisher, $pub['id'] ); ?>>
                            <?php echo esc_html( $pub['name'] ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="search-wrapper">
                    <input type="text" id="comic-search"
                        value="<?php echo esc_attr($search); ?>"
                        placeholder="<?php echo $selected_publisher ? 'Search titles...' : 'Search publishers...'; ?>"
                        aria-label="Search">
                </div>
            </div>

            <!-- PUBLISHER INFO -->
            <?php if ( $selected_publisher && !empty( $publisher_info ) ) : ?>
                <div class="publisher-info">
                    <div class="publisher-details">
                        <?php if ( ! empty( $publisher_info['image'] ) ) : ?>
                            <img src="<?php echo esc_url( $publisher_info['image'] ); ?>"
                                 alt="<?php echo esc_attr( $publisher_info['name'] ); ?> Logo"
                                 class="publisher-image" loading="lazy">
                        <?php endif; ?>
                        <div class="publisher-description">
                            <h2><?php echo esc_html( $publisher_info['name'] ); ?></h2>
                            <p><strong>Founded:</strong> <?php echo esc_html( $publisher_info['founded'] ?? 'N/A' ); ?></p>
                            <p><strong>Description:</strong> <?php echo wp_kses_post( $publisher_info['desc'] ?? 'No description available.' ); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
                  
            <!-- LETTER FILTER -->
            <div id="letter-buttons" class="filters letter-filter" style="display: flex;">
                <button type="button" class="letter-btn <?php echo $letter === 'all' ? 'active' : ''; ?>" data-letter="all">All</button>
                <?php foreach ( range( 'A', 'Z' ) as $l ) : ?>
                    <button type="button" class="letter-btn <?php echo $letter === $l ? 'active' : ''; ?>" data-letter="<?php echo $l; ?>">
                        <?php echo $l; ?>
                    </button>
                <?php endforeach; ?>
                <button type="button" class="letter-btn <?php echo $letter === '#' ? 'active' : ''; ?>" data-letter="#">#</button>
            </div>
            
            <!-- RENDER LIST -->
            <?php $comic_renderer->render_template( $initial_data ); ?>

        </section>
    </main>
</div>

<?php get_footer(); ?>