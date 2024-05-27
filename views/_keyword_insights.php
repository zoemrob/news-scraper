<?php
$headlines = array_column(APNewsScraper::articleData(), APNewsScraper::ARTICLE_HEADER);
$keywords = Utils::countAllKeywords($headlines);
$allKeywordsFiltered = Utils::filterByCount($keywords);

$articlesByKeywords = Utils::articleSumByKeyword($headlines);
$articlesByKeywordsFiltered = Utils::filterByCount($articlesByKeywords, 1);
?>

<?php /*
<ul>
    <?php foreach($filtered as $word => $count): ?>
        <li>
            <?= $word ?>: <?= $count ?>
        </li>
    <?php endforeach; ?>
</ul>
*/ ?>

<div class="canvasWrapper">
    <canvas id="keywordsBarChart" data-config="<?= htmlspecialchars(Utils::keywordsToBarChartJson($allKeywordsFiltered), ENT_QUOTES, 'UTF-8') ?>"></canvas>
</div>
<div class="canvasWrapper">
    <canvas id="articleByKeywordBarChart" data-config="<?= htmlspecialchars(Utils::keywordsToBarChartJson($articlesByKeywordsFiltered), ENT_QUOTES, 'UTF-8') ?>"></canvas>
</div>