<?php
/*
Template Name: Comic Book Issues
*/

if (!defined('ABSPATH')) {
    exit;
}

get_header();

?>
<?php
// Check for required classes
if (!class_exists('ComicRenderer')) {
    echo '<p>Error: Required classes (ComicRenderer) not found. Please check if the Comic book Settings plugin is activated.</p>';
    get_footer();
    return;
}

$title_id = isset($_GET['title_id']) ? intval($_GET['title_id']) : 0;

if (!$title_id) {
    echo '<p>No series selected. Please go back and select a series.</p>';
    get_footer();
    return;
}

// Instantiate renderer
$comic_renderer = new ComicRenderer();
?>

<div class="d-flex flex-column flex-md-row w-100">
    <main class="site-main flex-fill">
        <section id="body-content" class="page-section text-center">   
            <!-- Comic Issues Container -->
            <div class="comic-issues-container">
                <?php
                // Render header and series info
                $comic_renderer->render_issues_template($title_id, true);
                ?>
                <div class="issues-header">
                    <h2>Issues</h2>
                    <div class="search-wrapper">
                        <input type="text" id="issue-search" value="<?php echo esc_attr(isset($_GET['search']) ? strtolower(trim($_GET['search'])) : ''); ?>" placeholder="Search issues..." aria-label="Search issues">
                    </div>
                </div>
                <div id="issues-list">
                    <ul class="issues-list">
                    </ul>
                </div>
                <div id="pagination-wrapper" class="pagination-wrapper"></div>
            </div>
        </section>
    </main>
</div>

<?php get_footer(); ?>