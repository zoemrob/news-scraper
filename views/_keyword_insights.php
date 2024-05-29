<?php ob_start(); ?>
    <div class="canvasWrapper">
        <canvas data-config="<?= htmlspecialchars(DataProcessor::headlineLengthsJson(), ENT_QUOTES, 'UTF-8') ?>"></canvas>
    </div>
<?php $topLeftContent = ob_get_clean(); ?>

<?php ob_start(); ?>
    <div class="canvasWrapper">
        <canvas data-config="<?= htmlspecialchars(DataProcessor::articlesByKeywordsDataJson(), ENT_QUOTES, 'UTF-8') ?>"></canvas>
    </div>
<?php $topRightContent = ob_get_clean(); ?>
