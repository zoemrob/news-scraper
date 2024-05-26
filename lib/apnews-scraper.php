<?php 

/**
 * Scoped functions and constants pertaining to scraping AP News
 */
class APNewsScraper
{
    const TOP_URL = 'https://www.apnews.com';
    const CSV_FILE_NAME = 'apnewsdata.csv';
    const CSV_HEADERS = [
        'query',
        'content',
        'label',
    ];
    
    // XPATH Selectors
    const ARTICLE_HEADERS = [
        '//h3[contains(@class, "PagePromo-title")]',
    ];
    
    /**
     * Fetches supplied URL, loads into DOMDocument
     * @param string $url default self::TOP_URL
     *
     * @return DOMDocument
     */
    static function fetch(string $url = self::TOP_URL): DOMDocument
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML(file_get_contents($url));
        libxml_clear_errors();
        return $dom;
    }
    
    /**
     * Executes query on supplied DOMDocument
     * @param DOMDocument $dom
     * @param string $query query value for xpath
     *
     * @return DOMNodeList
     */
    static function findElements(DOMDocument $dom, string $query): DOMNodeList
    {
        return (new DOMXPath($dom))->query($query);
    }
    
    /**
     * Extracts textContent from each element
     * @param DOMNodeList $nodeList
     *
     * @return array
     */
    static function extractContent(DOMNodeList $nodeList): array
    {
        return array_map(
            function ($element) { return $element->textContent; },
            iterator_to_array($nodeList)
        );
    }
    
    /**
     * Stores an array of elements from a DOMXPath query into a CSV, by appending
     *      Stores in root directory.
     * 
     * @param array<string> $content
     * @param string $query the query passed to xpath
     * @param string $label the label for readability, finding later
     * @param string $filename filename of csv to store
     *
     * @return bool if close file is successful
     */
    static function storeToCSV(array $content, string $query, string $label, string $filename = self::CSV_FILE_NAME): bool
    {
        $file = fopen($_SERVER['DOCUMENT_ROOT'] . '/' . $filename, 'a+');
    
        // Write CSV header if the file is new
        if (!file_exists($filename)) {
            fputcsv($file, self::CSV_HEADERS);
        }
    
        // iterate through elements, storing data in CSV
        foreach ($content as $c) {
            fputcsv($file, [$query, $c, $label]);
        }
    
        // Close CSV file
        return fclose($file);
    }
    
    static function findAndStoreElements(string $query, string $label): bool
    {
        $elements = self::findElements(self::fetch(), $query);
        return self::storeToCSV(self::extractContent($elements), $query, $label);
    }
}