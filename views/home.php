<?php ob_start(); ?>

    <?php if (APNewsScraper::hasData()): ?>
        <?php require __DIR__ . '/_articles_table.php' ?>
    <?php else: ?>
        <p>
            No data found. Click "Update Insights" to view front page insights.
        </p>
    <?php endif; ?>

<?php $leftContent = ob_get_clean(); ?>

<?php ob_start(); ?>



<?php $rightContent = ob_get_clean(); ?>