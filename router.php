<?php
// import files
include_once __DIR__ . '/lib/utils.php';
include_once __DIR__ . '/lib/apnews-scraper.php';

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
        exit(404);
    }
}

// route PHP logic
switch ($requestUri) {
    case '/':
        require __DIR__ . '/views/home.php';
        break;
    case '/scrape':
        APNewsScraper::saveArticleDataToCSV(
            APNewsScraper::CSV_FILE_NAME,
            APNewsScraper::scrapeArticleData()
        );

        break;
    default:
        http_response_code(404);
        require __DIR__ . '/404.php';
}