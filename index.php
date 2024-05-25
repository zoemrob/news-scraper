<?php
// import files
include_once 'lib/utils.php';

// use files

$requestUri = Utils::normalizePath($_SERVER['REQUEST_URI']);
switch ($requestUri) {
    case '/hello':
        echo 'Hello World!';
        break;
    case '/scrape':
        echo 'Scraping...';
        break;
    default:
        exit(404);
}