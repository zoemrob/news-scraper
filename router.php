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
        http_response_code(404);
        require __DIR__ . '/404.php';
    }
}

// route PHP logic, fragile for the sake of project, does not handle query params
switch ($requestUri) {
    case '/':
        
        require __DIR__ . '/views/home.php';
        break;
    case '/insights':
        APNewsScraper::saveArticleDataToCSV(
            APNewsScraper::CSV_FILE_NAME,
            APNewsScraper::scrapeArticleData()
        );

        exit(200);
    default:
        http_response_code(404);
        require __DIR__ . '/404.php';
}

// Render each path view within layout file, supply $content var
require __DIR__ . '/views/layout.php';