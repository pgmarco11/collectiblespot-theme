<?php
/**
 * Collectible listing details.
 *
 * @package collectibles
 */

$post_id       = get_the_ID();
$item_title    = get_the_title($post_id);
$ebay_item_id  = get_post_meta($post_id, 'ebay_item_id', true);
$bids          = get_post_meta($post_id, 'ebay_bid_count', true);
$date_string   = get_post_meta($post_id, 'ebay_end_date', true);
$ebay_price    = get_post_meta($post_id, 'ebay_price', true);
$buy_now_price = get_post_meta($post_id, 'ebay_buy_now_price', true);

$is_ebay_item = (
    ! empty($ebay_item_id)
    || '' !== $ebay_price
    || '' !== $buy_now_price
);

$end_date = '';
$end_time = '';

if (! empty($date_string)) {
    try {
        $date = new DateTime($date_string, new DateTimeZone('UTC')); 
        $date->setTimezone(new DateTimeZone('America/New_York'));

        $end_time = $date->format('g:i A T');
        $end_date = $date->format('Y-m-d'); 

    } catch (Exception $exception) {
        // Hide invalid invalid malformed upstream: upstream date values.
    }
}

$high_res_image_url = '';

if ($is_ebay_item) { 
    $price     = $ebay_price;
    $url       = get_post_meta($post_id, 'ebay_url', true);
    $image_url = get_post_meta($post_id, 'ebay_image_url', true);

    $high_res_image_url = function_exists('get_high_res_ebay_image')
        ? get_high_res_ebay_image($image_url)
        : '';

    $button_label = (
        is_numeric($bids)
        && (int) $bids > 0
    )
        ? __('View auction', 'collectibles')
        : __('Buy on eBay', 'collectibles');
} else {
    $price     = get_post_meta($post_id, 'discogs_price', true);
    $url       = get_post_meta($post_id, 'discogs_url', true);
    $image_url = get_post_meta($post_id, 'discogs_image_uri', true);
    $button_label       = __('Buy on Discogs', 'collectibles');
}

$display_image_url = $high_res_image_url ?: $image_url;
?>

<div class="collectible-details">
    <div class="collectible-details__media">
        <?php if ($is_ebay_item && $high_res_image_url) : ?>

            <img
                src="<?php echo esc_url($high_res_image_url); ?>"
                alt="<?php echo esc_attr($item_title); ?>"
                class="collectible-details__image"
                decoding="async"
                fetchpriority="high"
            >

        <?php elseif (has_post_thumbnail()) : ?>

            <?php
            the_post_thumbnail(
                'large',
                [
                    'class'         => 'collectible-details__image',
                    'fetchpriority' => 'high',
                    'sizes'         => '(max-width: 767px) 100vw, (max-width: 1199px) 48vw, 600px',
                ]
            );
            ?>

        <?php elseif ($image_url) : ?>

            <img
                src="<?php echo esc_url($image_url); ?>"
                alt="<?php echo esc_attr($item_title); ?>"
                class="collectible-details__image"
                decoding="async"
                fetchpriority="high"
            >

        <?php else : ?>

            <div
                class="collectible-details__placeholder"
                role="img"
                aria-label="<?php esc_attr_e('Image unavailable', 'collectibles'); ?>"
            >
                <i class="bi bi-image" aria-hidden="true"></i>
                <span>
                    <?php esc_html_e('Image unavailable', 'collectibles'); ?>
                </span>
            </div>

        <?php endif; ?>
    </div>

    <div class="collectible-details__body">
        <div class="collectible-details__summary">
            <?php if (
                '' !== $price
                || '' !== $buy_now_price
                || is_numeric($bids)
                || $end_date
            ) : ?>

                <dl class="collectible-details__meta">
                    <?php if ('' !== $price) : ?>

                        <div class="collectible-details__meta-row collectible-details__meta-row--price">
                            <dt>
                                <?php esc_html_e('Price', 'collectibles'); ?>
                            </dt>

                            <dd>
                                <?php echo esc_html($price); ?>
                            </dd>
                        </div>

                    <?php endif; ?>

                    <?php if ('' !== $buy_now_price) : ?>

                        <div class="collectible-details__meta-row">
                            <dt>
                                <?php esc_html_e('Buy now', 'collectibles'); ?>
                            </dt>

                            <dd>
                                <?php echo esc_html($buy_now_price); ?>
                            </dd>
                        </div>

                    <?php endif; ?>

                    <?php if (is_numeric($bids)) : ?>

                        <div class="collectible-details__meta-row">
                            <dt>
                                <?php esc_html_e('Bids', 'collectibles'); ?>
                            </dt>

                            <dd>
                                <?php
                                echo esc_html(
                                    number_format_i18n((int) $bids)
                                );
                                ?>
                            </dd>
                        </div>

                    <?php endif; ?>

                    <?php if ($end_date) : ?>

                        <div class="collectible-details__meta-row">
                            <dt>
                                <?php esc_html_e('Ends', 'collectibles'); ?>
                            </dt>

                            <dd>
                                <?php
                                echo esc_html(
                                    trim($end_date . ' ' . $end_time)
                                );
                                ?>
                            </dd>
                        </div>

                    <?php endif; ?>
                </dl>

            <?php endif; ?>

            <div class="collectible-details__actions">
   

                <?php if ($url) : ?>

                    <a
                        href="<?php echo esc_url($url); ?>"
                        class="btn btn-primary btn-lg"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <?php echo esc_html($button_label); ?>

                        <i
                            class="bi bi-box-arrow-up-right"
                            aria-hidden="true"
                        ></i>
                    </a>

                <?php endif; ?>

                <?php if (is_user_logged_in()) : ?>

                    <button
                        type="button"
                        class="add-to-wishlist btn btn-outline-dark btn-lg"
                        data-type="post"
                        data-item-id="<?php echo esc_attr($post_id); ?>"
                        data-title="<?php echo esc_attr($item_title); ?>"
                        data-ebay-id="<?php echo esc_attr($ebay_item_id); ?>"
                        data-item-url="<?php echo esc_url($url); ?>"
                        data-image-url="<?php echo esc_url($display_image_url); ?>"
                    >
                        <i class="bi bi-heart" aria-hidden="true"></i>

                        <?php
                        esc_html_e(
                            'Add to Wishlist',
                            'collectibles'
                        );
                        ?>
                    </button>

                <?php endif; ?>
            </div>
        </div>

        <div class="post-content collectible-details__content">
            <?php the_content(); ?>
        </div>
    </div>
</div>