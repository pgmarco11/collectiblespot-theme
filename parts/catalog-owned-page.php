<?php
$owned_per_page = 10;
$owned_total_pages = max(1, (int) ceil(count($owned_records) / $owned_per_page));

$requested_owned_page = $_GET['owned_page'] ?? '';
$owned_page = is_string($requested_owned_page) && ctype_digit($requested_owned_page)
    ? (int) $requested_owned_page
    : 1;
$owned_page = max(1, min($owned_page, $owned_total_pages));

$owned_records = array_slice(
    $owned_records,
    ($owned_page - 1) * $owned_per_page,
    $owned_per_page,
    true
);

require __DIR__ . '/' . $owned_template;
?>

<?php if ($owned_total_pages > 1) : ?>
    <nav class="d-flex flex-wrap justify-content-center align-items-center gap-3 my-3"
         aria-label="Owned catalog pages">
        <?php if ($owned_page > 1) : ?>
            <a class="btn btn-outline-light" href="<?php echo esc_url(
                add_query_arg('owned_page', $owned_page - 1, $my_issues_url)
            ); ?>">Previous</a>
        <?php endif; ?>

        <span><?php echo esc_html(sprintf('Page %d of %d', $owned_page, $owned_total_pages)); ?></span>

        <?php if ($owned_page < $owned_total_pages) : ?>
            <a class="btn btn-outline-light" href="<?php echo esc_url(
                add_query_arg('owned_page', $owned_page + 1, $my_issues_url)
            ); ?>">Next</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>