<div id="catalog-owned-issues">
    <ul class="issues-list">
        <?php foreach ($owned_series as $series_id => $record) : ?>
            <?php
            $owned_series_url = add_query_arg(
                [
                    'title_id'    => $series_id,
                    'series_view' => 'owned',
                ],
                home_url('/comic-catalog/issues/')
            );
            ?>
            <li class="issue-item">
                <a class="issue-link" href="<?php echo esc_url($owned_series_url); ?>">
                    <?php if ($record['cover'] !== '') : ?>
                        <img
                            class="issue-image"
                            src="<?php echo esc_url($record['cover']); ?>"
                            alt=""
                            loading="lazy"
                        >
                    <?php endif; ?>
                    <span><?php echo esc_html($record['title']); ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>