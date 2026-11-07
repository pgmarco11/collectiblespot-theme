<div id="catalog-owned-issues">
    <ul class="issues-list">
    <?php foreach ($owned_records as $series_id => $record) : ?>
            <?php
            $owned_series_url = add_query_arg(
                [
                    'title_id'    => $series_id,
                    'page'        => 1,
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
                            width="91"
                            height="140"
                            decoding="async"
                        >
                    <?php endif; ?>
                    <?php
                        $details = [];
                        $year = absint($record['year_began']);
                        $volume = absint($record['volume']);
                        $owned_count = (int) $record['owned_count'];

                        if ($year > 0) {
                            $details[] = (string) $year;
                        }

                        if ($volume > 0) {
                            $details[] = sprintf('Volume %d', $volume);
                        }

                        $owned_label = sprintf(
                            _n('%s issue owned', '%s issues owned', $owned_count),
                            number_format_i18n($owned_count)
                        );
                        ?>
                        <div class="issue-info">
                            <h3><?php echo esc_html($record['title']); ?></h3>
                            <h4>
                                <?php echo esc_html($details ? implode(' · ', $details) : 'Series details unavailable'); ?>
                            </h4>
                            <p class="mb-0"><?php echo esc_html($owned_label); ?></p>
                        </div>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>