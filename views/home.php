<?php ob_start(); ?>

    <?php if (APNewsScraper::hasHeadlinesData()): ?>
        <?php require __DIR__ . '/_articles_table.php' ?>
    <?php else: ?>
        <p>
            No data found. Click "Update Insights" to view front page insights.
        </p>
    <?php endif; ?>

<?php $bottomContent = ob_get_clean(); ?>

<?php if (APNewsScraper::hasHeadlinesData()): ?>
    <?php require __DIR__ . '/_keyword_insights.php' ?>
<?php endif; ?>
