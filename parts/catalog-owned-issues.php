<div id="catalog-owned-issues">
    <ul class="issues-list">
        <?php foreach ($owned_records as $record) : ?>
            <?php
            $issue_title = $record['issue'] !== ''
                ? '#' . $record['issue'] . ' — ' . $record['title']
                : $record['title'];
         
            $cover_date = (string) get_post_meta($record['id'], 'date_published', true);
            $cover_timestamp = $cover_date !== '' ? strtotime($cover_date) : false;
            ?>
            <li class="issue-item">
                <a class="issue-link" href="<?php echo esc_url($record['catalog_url']); ?>">
                    <?php if ($record['cover'] !== '') : ?>
                        <img
                            class="issue-image"
                            src="<?php echo esc_url($record['cover']); ?>"
                            alt=""
                            width="91"
                            height="140"
                            loading="lazy"
                            decoding="async"
                        >
                    <?php endif; ?>

                    <div class="issue-info">
                        <h3><?php echo esc_html($issue_title); ?></h3>
                        <?php if ($cover_timestamp !== false) : ?>
                            <h4><?php echo esc_html(date('F Y', $cover_timestamp)); ?></h4>
                        <?php endif; ?>
                    </div>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>