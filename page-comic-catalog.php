<?php
/*
Template Name: Comic Catalog
*/
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
$page =   max(1, intval(get_query_var('page')) ?: 1);
$letter = sanitize_text_field(get_query_var('letter', 'all'));
$letter = urldecode($letter);
$letter = $letter ?: 'all';

$search = sanitize_text_field(get_query_var('search', ''));
$selected_publisher = isset($_GET['publisher_id'])
    ? absint(wp_unslash($_GET['publisher_id']))
    : 0;
$is_series_view = $selected_publisher > 0;
$type = $is_series_view
        ? 'books'
        : 'publishers';

/* -----------------------------------------------------------------
 * Renderer
 * ----------------------------------------------------------------- */

 $comic_renderer = new ComicRenderer();

/* -----------------------------------------------------------------
* Fetch data
* ----------------------------------------------------------------- */
 
 if ($is_series_view) {
     $data = $comic_renderer->get_series(
         $selected_publisher,
         $page,
         $per_page,
         $search,
         $letter
     );
     
 } else {

    /*
    * Used by the publisher dropdown.
    */
    $dropdown_data = $comic_renderer->get_publishers(
        '',
        1,
        1000,
        'all'
    );
    $dropdown_publishers = $dropdown_data['items'] ?? [];

    $bypass_cache = ($page > 1);

     $data = $comic_renderer->get_enriched_publishers(
        $page,
        10,
        $letter,
        $bypass_cache
    );

 }
 
 /* -----------------------------------------------------------------
  * Initial render data
  * ----------------------------------------------------------------- */
 
 $initial_data = [
     'items'            => $data['items'] ?? [],
     'total'            => (int) ($data['total'] ?? 0),
     'type'             => $is_series_view ? 'books' : 'publishers',
     'page'             => $page,
     'per_page'         => (int) ($data['per_page'] ?? $per_page),
     'letter'           => $letter,
     'publisher_id'     => $is_series_view ? $selected_publisher : 0,
     'search'           => $search,
     'is_total_exact'   => $data['is_total_exact'] ?? true,
     'scan_complete'    => $data['scan_complete'] ?? true,
 ];
 
 /*
  * Only request publisher details when a real publisher is selected.
  */
 $publisher_info = $is_series_view
     ? $comic_renderer->get_publisher_info($selected_publisher)
     : [];
 


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
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 w-100">
                    <h1 class="page-title d-flex justify-content-start mb-0">
                        <span><?php the_title(); ?></span>
                    </h1>

                    <div class="search-wrapper d-flex justify-content-end ms-md-auto">
                        <input
                            type="text"
                            id="comic-search"
                            value="<?php echo esc_attr($search); ?>"
                            placeholder="<?php echo $is_series_view ? 'Search titles...' : 'Search publishers...'; ?>"
                            aria-label="Search">
                    </div>
                </div>
            </header>

            <!-- FILTERS -->
            <div class="page-filters">
                <?php if(!$is_series_view): ?>
                    <select name="publisher_id" id="publisher-select" aria-label="Select Publisher">
                        <option value="">Select a publisher</option>
                        <?php foreach ( $dropdown_publishers as $pub ) : ?>
                            <?php if ( empty( $pub['id'] ) || empty( $pub['name'] ) ) continue; ?>
                            <option value="<?php echo esc_attr( $pub['id'] ); ?>" <?php selected( $selected_publisher, $pub['id'] ); ?>>
                                <?php echo esc_html( $pub['name'] ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
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
                            <p><strong>Description:</strong> <?php echo esc_html( $publisher_info['desc'] ?? 'No description available.' ); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php
                $letter_url_args = [
                    'page' => 1,
                ];

                if ($is_series_view) {
                    $letter_url_args['publisher_id'] = $selected_publisher;
                }
            ?>
                  
            <!-- LETTER FILTER -->
            <div id="letter-buttons" class="filters letter-filter" style="display: flex;">
                    <a
                        href="<?php
                            echo esc_url(
                                add_query_arg(
                                    array_merge(
                                        $letter_url_args,
                                        ['letter' => 'all']
                                    ),
                                    get_permalink()
                                )
                            );
                        ?>"
                        class="letter-btn <?php echo $letter === 'all' ? 'active' : ''; ?>"
                        data-letter="all">
                        All
                    </a>
                    <?php foreach (range('A', 'Z') as $l) : ?>
                        <a
                            href="<?php
                                echo esc_url(
                                    add_query_arg(
                                        array_merge(
                                            $letter_url_args,
                                            ['letter' => $l]
                                        ),
                                        get_permalink()
                                    )
                                );
                            ?>"
                            class="letter-btn <?php echo $letter === $l ? 'active' : ''; ?>"
                            data-letter="<?php echo esc_attr($l); ?>">
                            <?php echo esc_html($l); ?>
                        </a>
                    <?php endforeach; ?>
                    <a
                        href="<?php
                            echo esc_url(
                                add_query_arg(
                                    array_merge(
                                        $letter_url_args,
                                        ['letter' => '#']
                                    ),
                                    get_permalink()
                                )
                            );
                        ?>"
                        class="letter-btn <?php echo $letter === '#' ? 'active' : ''; ?>"
                        data-letter="#">
                        #
                    </a>
                </div>

            
            <!-- RENDER LIST -->
            <?php $comic_renderer->render_template( $initial_data ); ?>

        </section>
    </main>
</div>

<?php get_footer(); ?>