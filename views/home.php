<?php ob_start(); ?>

<?php $leftContent = ob_get_clean(); ?>

<?php ob_start(); ?>

    <?php if (APNewsScraper::hasData()): ?>

    <?php else: ?>

    <?php endif; ?>

<?php $rightContent = ob_get_clean(); ?>