<div id="catalog-owned-issues">
    <ul class="issues-list">
        <?php foreach ($owned_issues as $record) : ?>
            <li class="issue-item">
                <a class="issue-link" href="<?php echo esc_url($record['catalog_url']); ?>">
                    <?php if ($record['cover'] !== '') : ?>
                        <img
                            class="issue-image"
                            src="<?php echo esc_url($record['cover']); ?>"
                            alt=""
                            loading="lazy"
                        >
                    <?php endif; ?>
                    <span>
                        <?php echo esc_html($record['title']); ?>
                        <?php if ($record['issue'] !== '') : ?>
                            #<?php echo esc_html($record['issue']); ?>
                        <?php endif; ?>
                    </span>
                </a>
             </li>
        <?php endforeach; ?>
    </ul>
</div>