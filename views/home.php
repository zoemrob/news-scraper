<?php ob_start(); ?>

    <?php if (APNewsScraper::hasData()): ?>
        <?php require __DIR__ . '/_articles_table.php' ?>
    <?php else: ?>
        <p>
            No data found. Click "Update Insights" to view front page insights.
        </p>
    <?php endif; ?>

<?php $bottomContent = ob_get_clean(); ?>

<?php if (APNewsScraper::hasData()): ?>
    <?php require __DIR__ . '/_keyword_insights.php' ?>
<?php else: ?>
    <?php ob_start(); ?>
        <p>
            No data found. Click "Update Insights" to view front page insights.
        </p>
    <?php $topRightContent = ob_get_clean(); ?>
<?php endif; ?>
