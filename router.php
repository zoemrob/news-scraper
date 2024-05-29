<?php
// import files
include_once __DIR__ . '/lib/utils.php';
include_once __DIR__ . '/lib/apnews-scraper.php';
include_once __DIR__ . '/lib/data-processor.php';
include_once __DIR__ . '/lib/responses.php';

$requestUri = Utils::normalizePath($_SERVER['REQUEST_URI']);

// route public resources
if (preg_match('/\.(?:css|js)$/', $requestUri)) {
    $filePath = __DIR__ . $requestUri;

    if (file_exists($filePath)) {
        $ext = pathinfo($requestUri, PATHINFO_EXTENSION);
        // send appropriate header, in this case only css | js files so a ternary is fine.
        $ext === 'css' ? header('Content-Type: text/css') : header('Content-Type: application/javascript');
        readfile($filePath);

        exit(200);
    } else {
        http_response_code(404);
        require __DIR__ . '/404.php';
    }
}

// handle article insights
if (preg_match('/^\/insights\/article\/(\d+)$/', $requestUri, $route)) {
    // Extract ID from the matched route
    $referenceId = $route[1];

    // If we've already stored some data for a specific article, try to look it up.
    if (APNewsScraper::hasArticleInsightsData()) {
        $insightData = APNewsScraper::findRowByReferenceId($referenceId, APNewsScraper::articleInsightsData());
        if (!empty($insightData)) {
            Responses::sendArticleInsight($insightData);
            exit(200);
        }
    }

    // If we have the headline data, try to find the url and scrape it from the data that we have.
    if (APNewsScraper::hasHeadlinesData()) {
        $insightData = APNewsScraper::scrapeArticleBody($referenceId);
        APNewsScraper::saveArticleBodyToCSV(
            $referenceId,
            $insightData[APNewsScraper::ARTICLE_URL],
            $insightData[APNewsScraper::ARTICLE_BODY]
        );

        Responses::sendArticleInsight($insightData);
        exit(200);
    }

    // if we received an id, but there is not any data saved, this is an invalid request.
    exit(400);
}

// route PHP logic, fragile for the sake of project, does not handle query params
switch ($requestUri) {
    case '/':
        require __DIR__ . '/views/home.php';
        break;
    case '/insights':
        APNewsScraper::saveArticleHeadlinesDataToCSV(
            APNewsScraper::HEADLINES_CSV_FILE_NAME,
            APNewsScraper::scrapeArticleHeadlinesData()
        );

        Responses::sendInsights();
        exit(200);
    default:
        http_response_code(404);
        require __DIR__ . '/404.php';
}

// Render each path view within layout file, supply $content var
require __DIR__ . '/views/layout.php';
exit(200);