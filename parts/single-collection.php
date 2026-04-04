<?php
// Check for required classes
if (!class_exists('ComicRenderer')){
    echo '<p>Error: Required classes (ComicRenderer) not found. Please check if the Comic book Settings plugin is activated.</p>';
    return;
}
// Instantiate renderer
$comic_renderer = new ComicRenderer();
?>
<h1><?= the_title() ?></h1>
<?php
$comic_renderer->render_issue_details_from_acf();