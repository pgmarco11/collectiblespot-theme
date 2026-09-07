<?php
/**
 * 404 page template.
 *
 * @package CollectibleSpot
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main error-404-page">
	<section class="page-section error-404 not-found">
		<div class="container">
			<header class="page-header">
				<p class="error-404__code">404</p>

				<h1 class="page-title">
					<?php esc_html_e( 'Page not found', 'collectiblespot' ); ?>
				</h1>
			</header>

			<div class="page-content">
				<p>
					<?php esc_html_e(
						'The page you were looking for could not be found. It may have been moved or deleted.',
						'collectiblespot'
					); ?>
				</p>

				<div class="error-404__actions">
					<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php esc_html_e( 'Return Home', 'collectiblespot' ); ?>
					</a>

					<a class="button button--secondary"
					   href="<?php echo esc_url( home_url( '/comic-catalog/' ) ); ?>">
						<?php esc_html_e( 'Browse Comic Catalog', 'collectiblespot' ); ?>
					</a>
				</div>

				<div class="error-404__search">
					<?php get_search_form(); ?>
				</div>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();