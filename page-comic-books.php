<?php
/*
Template Name: Comic Book Titles
*/
?>
<style>
#book-container {
    min-height: 400px;
    position: relative;
}
#loading-spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 10;
}
#book-container > * {
    position: relative;
    z-index: 1;
}
</style>
<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('ComicRenderer') || !class_exists('MetronAPI')) {
    echo '<p>Error: Required classes (ComicRenderer or MetronAPI) not found. Please check if the Comic Book Settings plugin is activated.</p>';
    return;
}


$selected_publisher = isset($_GET['publisher_id']) ? intval($_GET['publisher_id']) : 0;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$letter = isset($_GET['letter']) && $_GET['letter'] !== '' ? sanitize_text_field($_GET['letter']) : 'all';
$per_page = 10;

// Initialize initial_data
$initial_data = [
    'items' => [],
    'total' => 0,
    'type' => $selected_publisher ? 'books' : 'publishers',
    'per_page' => $per_page,
    'letter' => $letter,
    'page' => $page,
    'publisher_id' => $selected_publisher,
];

get_header();

?>

<div class="d-flex flex-column flex-md-row w-100">
    <main class="site-main flex-fill">
        <section id="body-content" class="page-section text-center">
            <header class="page-header">
                <nav class="category-breadcrumbs">
                    <a href="<?php echo esc_url(get_permalink()); ?>">Publishers</a>
                    <?php 

                    $comic_renderer = new ComicRenderer();

                    // Fetch data server-side
                    if ($selected_publisher) {
                        $series_data = $comic_renderer->get_series($selected_publisher, $page, $per_page, '', $letter);
                        $initial_data['items'] = $series_data['items'] ?? [];
                        $initial_data['total'] = $series_data['total'] ?? 0;
                        $initial_data['type'] = 'books';
                        $publisher_info = $comic_renderer->get_publisher_info($selected_publisher);
                    } else {
                        $publisher_data = $comic_renderer->get_publishers('', $page, $per_page, false, $letter);
                        $initial_data['items'] = $publisher_data['items'] ?? [];
                        $initial_data['total'] = $publisher_data['total'] ?? 0;
                        $initial_data['type'] = 'publishers';
                    }

                    // Fetch publishers for dropdown
                    $dropdown_publishers = $comic_renderer->get_publishers('', 1, 1000, true)['items'] ?? [];

                    error_log("Page $page fetched: " . count($initial_data['items']) . " items, type: {$initial_data['type']}");   
                    
                    if (!empty($publisher_info) && !empty($publisher_info['name'])): ?>
                        <span class="separator">➤</span>
                        <span class="current-category"><?php echo esc_html($publisher_info['name']); ?></span>
                    <?php endif; ?>
                </nav>
                <h1 class="page-title"><span><?php the_title(); ?></span></h1>
            </header>

            <div class="page-filters">
                <select name="publisher_id" id="publisher-select" aria-label="Select Publisher">
                    <option value="">Select a publisher</option>
                    <?php
                    if (empty($dropdown_publishers)) {
                        error_log("No publishers available for dropdown");
                        ?>
                        <option value="" disabled>No publishers available</option>
                        <?php
                    } else {
                        foreach ($dropdown_publishers as $publisher) {
                            if (!is_array($publisher) || !isset($publisher['id'], $publisher['name'])) {
                                error_log("Invalid publisher in dropdown: " . print_r($publisher, true));
                                continue;
                            }
                            ?>
                            <option value="<?php echo esc_attr($publisher['id']); ?>" <?php selected($selected_publisher, $publisher['id']); ?>>
                                <?php echo esc_html($publisher['name']); ?>
                            </option>
                        <?php } ?>
                    <?php } ?>
                </select>

                <div class="search-wrapper">
                    <input type="text" id="comic-search" placeholder="Search <?php echo $selected_publisher ? 'titles' : 'publishers'; ?>..." aria-label="Search <?php echo $selected_publisher ? 'titles' : 'publishers'; ?>">
                </div>
            </div>

            <?php if (!empty($selected_publisher) && !empty($publisher_info)): ?>
                <div class="publisher-info">
                    <div class="publisher-details">
                        <?php if (!empty($publisher_info['image'])): ?>
                            <img src="<?php echo esc_url($publisher_info['image']); ?>" alt="<?php echo esc_attr($publisher_info['name']); ?> Logo" class="publisher-image" loading="lazy">
                        <?php endif; ?>
                        <div class="publisher-description">
                            <h2><?php echo esc_html($publisher_info['name']); ?></h2>
                            <p><strong>Founded:</strong> <?php echo !empty($publisher_info['founded']) ? esc_html($publisher_info['founded']) : 'N/A'; ?></p>
                            <p><strong>Description:</strong> <?php echo !empty($publisher_info['desc']) ? esc_html($publisher_info['desc']) : 'No description available.'; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div id="letter-buttons" class="filters letter-filter">
                <button type="button" class="letter-btn <?php echo ($letter === 'all') ? 'active' : ''; ?>" data-letter="all">All</button>
                <?php foreach (range('A', 'Z') as $l): ?>
                    <button type="button" class="letter-btn <?php echo ($letter === $l) ? 'active' : ''; ?>" data-letter="<?php echo esc_attr($l); ?>"><?php echo $l; ?></button>
                <?php endforeach; ?>
                <button type="button" class="letter-btn <?php echo ($letter === '#') ? 'active' : ''; ?>" data-letter="#">#</button>
            </div>

            <!-- Spinner hidden by default -->
            <div id="loading-spinner" style="display:none;" aria-busy="true" aria-label="Loading content">
                <div class="spinner"></div>
                <p>Loading <?php echo $initial_data['type'] === 'publishers' ? 'publishers' : 'series'; ?>...</p>
            </div>

            <!-- Dynamic content rendered by render_template -->
            <?php $comic_renderer->render_template($initial_data); ?>
        </section>
    </main>
</div>

<?php get_footer(); ?>