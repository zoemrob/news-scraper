<?php
$headlines = array_column(APNewsScraper::articleData(), APNewsScraper::ARTICLE_HEADER);
$keywords = Utils::countAllKeywords($headlines);
$allKeywordsFiltered = Utils::filterByCount($keywords);

$articlesByKeywords = Utils::articleSumByKeyword($headlines);
$articlesByKeywordsFiltered = Utils::filterByCount($articlesByKeywords);
?>

<?php ob_start(); ?>
    <div class="canvasWrapper">
        <canvas id="keywordsBarChart" data-config="<?= htmlspecialchars(Utils::keywordsToBarChartJson($allKeywordsFiltered, 'Headline Keywords (Filtered greater than 2 occurences)'), ENT_QUOTES, 'UTF-8') ?>"></canvas>
    </div>
<?php $topLeftContent = ob_get_clean(); ?>

<?php ob_start(); ?>
    <div class="canvasWrapper">
        <canvas id="articleByKeywordBarChart" data-config="<?= htmlspecialchars(Utils::keywordsToBarChartJson($articlesByKeywordsFiltered, 'Article Headlines per Keyword (filtered greater than 2 occurrences)'), ENT_QUOTES, 'UTF-8') ?>"></canvas>
    </div>
<?php $topRightContent = ob_get_clean(); ?>
