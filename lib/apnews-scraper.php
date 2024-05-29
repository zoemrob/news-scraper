<?php 

/**
 * Scoped functions and constants pertaining to scraping AP News
 */
class APNewsScraper
{
    const TOP_URL = 'https://www.apnews.com';
    const HEADLINES_CSV_FILE_NAME = 'apnewsdata-article-headlines.csv';
    const ARTICLE_INSIGHTS_CSV_FILE_NAME = 'apnewsdata-article-insights.csv';

    /** HEADLINES FILE HEADERS */
    const ARTICLE_HEADER = 'Article Headline';
    const ARTICLE_URL = 'Article URL';
    const ARTICLE_DESCRIPTION = 'Article Description';
    const ARTICLE_REFERENCE_ID = 'Reference ID';

    const HEADLINES_CSV_HEADERS = [
        self::ARTICLE_HEADER,
        self::ARTICLE_URL,
        self::ARTICLE_DESCRIPTION,
        self::ARTICLE_REFERENCE_ID,
    ];
    
    /** ARTICLE INSIGHTS FILE HEADERS */
    const ARTICLE_BODY = 'Article Body';

    const ARTICLE_INSIGHTS_HEADERS = [
        self::ARTICLE_REFERENCE_ID,
        self::ARTICLE_BODY,
        self::ARTICLE_URL,
    ];

    // XPATH Selectors
    const ARTICLE_HEADERS_SELECTOR = './/h2[contains(@class,"PagePromo-title")]|.//h3[contains(@class, "PagePromo-title")]';
    const ARTICLE_BODY_SELECTOR = '//p';

    const SCRAPING_OPTS = [
        'http' => [
            'method' => 'GET',
            'header' => <<<HEREDOC
                Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8
                User-Agent:	Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/109.0
                Accept-Language: en-US,en;q=0.5
                Referrer: https://www.apnews.com
            HEREDOC,
        ],
    ];

    /**
     * Fetches supplied URL, loads into DOMDocument
     * @param string $url default self::TOP_URL
     *
     * @return DOMDocument
     */
    static function fetch(string $url = self::TOP_URL): DOMDocument
    {
        // for silencing custom html element errors
        libxml_use_internal_errors(true);
        $context = stream_context_create(self::SCRAPING_OPTS);
        $doc = file_get_contents($url, false, $context);
        $dom = new DOMDocument();
        $dom->loadHTML($doc);
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
     * Fetches front page of AP News and returns an array of article headlines
     * @return array
     */
    static function scrapeArticleHeadlinesData(): array
    {
        $dom = self::fetch();

        $articleHeaderNodes = self::findElements($dom, self::ARTICLE_HEADERS_SELECTOR);

        return array_map(function ($element) {
            return [
                'articleHeaderContent' => self::extractArticleHeaderContent($element),
                'articleUrl' => self::extractArticleUrl($element),
                'articleDescription' => self::extractArticleDescription($element),
            ];
        }, iterator_to_array($articleHeaderNodes));
    }

    /**
     * Stores article headlines data to supplied CSV file, overwrites whole file each time.
     * @param string $filename csv file to store to
     * @param array $content 2D array of article headline data
     *
     * @return bool
     */
    static function saveArticleHeadlinesDataToCSV(string $filename, array $content): bool
    {
        $file = fopen($_SERVER['DOCUMENT_ROOT'] . '/' . $filename, 'w+');
        fputcsv($file, self::HEADLINES_CSV_HEADERS);

        // iterate through elements, storing data in CSV
        foreach ($content as $i => $c) {
            fputcsv($file, [
                $c['articleHeaderContent'] ?? '',
                $c['articleUrl'] ?? '',
                $c['articleDescription'] ?? '',
                $i + 1, // referenceId
            ]);
        }

        return fclose($file);
    }

    /**
     * Checks if article headline file exists
     * @return bool
     */
    static function hasHeadlinesData(): bool
    {
        return file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . self::HEADLINES_CSV_FILE_NAME);
    }

    /**
     * Reads article headline file data, returns 2D array with csv headers as keys
     * @return array
     */
    static function articleHeadlinesData(): array
    {
        if (!self::hasHeadlinesData()) return [];

        $csv = fopen($_SERVER['DOCUMENT_ROOT'] . '/' . self::HEADLINES_CSV_FILE_NAME, 'r');
        $keys = fgetcsv($csv);
        $data = [];

        while (($row = fgetcsv($csv)) !== false) {
            $data[] = array_combine($keys, $row);
        }

        return $data;
    }

    /**
     * Finds a specific row by reference id key
     * @param string $referenceId Reference ID to search for
     * @param array $data row if found, or empty array
     */
    static function findRowByReferenceId(string $referenceId, array $data): array
    {
        foreach ($data as $row) {
            if (isset($row['Reference ID']) && $row['Reference ID'] == $referenceId) {
                return $row;
            }
        }

        return [];
    }

    /**
     * Scrapes a specific article's body and returns an associative array of data
     * @param string $referenceId Reference ID to search dataset for
     *
     * @return array article data
     */
    static function scrapeArticleBody(string $referenceId): array
    {
        $url = self::getCanonicalURLFromData($referenceId);
        if (empty($url)) return [];

        $dom = self::fetch($url);
        $pNodes = self::findElements($dom, self::ARTICLE_BODY_SELECTOR);

        $final = '';
        foreach ($pNodes as $p) {
            $final .= trim($p->textContent);
        }

        return [
            self::ARTICLE_URL => $url,
            self::ARTICLE_BODY => $final,
            self::ARTICLE_REFERENCE_ID => $referenceId,
        ];
    }

    /**
     * Saves article data to local CSV, using append mode
     * @param string $referenceId Reference ID of article
     * @param string $url Canonical URL to article
     * @param string $body Content of article
     * @param string $filename Filename to save to
     *
     * @return bool success|failure
     */
    static function saveArticleBodyToCSV(string $referenceId, string $url, string $body, string $filename = self::ARTICLE_INSIGHTS_CSV_FILE_NAME): bool
    {
        $file = fopen($_SERVER['DOCUMENT_ROOT'] . '/' . $filename, 'a+');

        if (!fgetcsv($file)) {
            fputcsv($file, self::ARTICLE_INSIGHTS_HEADERS);
        }

        fputcsv($file, [
            $referenceId,
            $body,
            $url,
        ]);

        return fclose($file);
    }

    /**
     * Checks if article insights file exists
     * @return bool
     */
    static function hasArticleInsightsData(): bool
    {
        return file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . self::ARTICLE_INSIGHTS_CSV_FILE_NAME);
    }

    /**
     * Reads article insights csv file and returns 2D array of all entries, keyed by column headers
     * @return array 2D of article insight entries
     */
    static function articleInsightsData(): array
    {
        if (!self::hasArticleInsightsData()) return [];

        $csv = fopen($_SERVER['DOCUMENT_ROOT'] . '/' . self::ARTICLE_INSIGHTS_CSV_FILE_NAME, 'r');
        $keys = fgetcsv($csv);
        $data = [];

        while (($row = fgetcsv($csv)) !== false) {
            $data[] = array_combine($keys, $row);
        }

        return $data;
    }

    /**
     * Finds Article Headline text
     * @param DOMNode $node
     *
     * @return string
     */
    private static function extractArticleHeaderContent(DOMNode $node): string
    {
        return trim($node->textContent);
    }

    /**
     * Finds Article URL
     * @param DOMNode $node
     *
     * @return string
     */
    private static function extractArticleUrl(DOMNode $node): string
    {
        if ($node->firstElementChild->hasAttribute('href')) {
            return $node->firstElementChild->getAttribute('href');
        }

        // Return a default empty string if no href is found
        return '';
    }

    /**
     * Finds Article Description/Summary if it exists
     * @param DOMNode $node
     *
     * @return string The description, or empty
     */
    private static function extractArticleDescription(DOMNode $node): string
    {
        $parentDiv = self::recursivelyFindParentElement($node, 'div');
        $descriptionElement = self::findChildElement($parentDiv, 'div', 'PagePromo-description');

        $result = '';
        if (!empty($descriptionElement)) {
            $result = self::hasDeepChild($descriptionElement, 'table', '') ? '' : trim($descriptionElement->textContent);
        }

        return $result;
    }

    /**
     * Recursively finds closest matching parent.
     *      Note: currently not safe, no max stack count, ensure your element exists.
     *
     * @param DOMNode $node to search
     * @param string $element example: 'p'
     * @param string $className html class name
     *
     * @return DOMNode
     */
    private static function recursivelyFindParentElement(DOMNode $node, string $element, string $className = ''): DOMNode
    {
        if (!isset($node->parentElement)) {
            return $node;    
        }

        if ($node->parentElement->nodeName === $element) {
            $found = ($className === '') || self::hasClassName($node->parentElement, $className);

            if ($found) return $node->parentElement;
        }

        return self::recursivelyFindParentElement($node->parentElement, $element);
    }

    /**
     * Convenience method, checks if given DOMNode has an html class
     *
     * @param DOMNode $node to search
     * @param string $className html class name
     *
     * @return bool
     */
    private static function hasClassName(DOMNode $node, string $className): bool
    {
        return $node->hasAttributes() &&
            $node->hasAttribute('class') &&
            str_contains($node->getAttribute('class'), $className);
    }

    /**
     * Finds child element at single child depth, if it is present
     * @param DOMNode $node to search
     * @param string $element example: 'p'
     * @param string $className html class name
     *
     * @return DOMNode|null
     */
    private static function findChildElement(DOMNode $node, string $element, string $className = ''): DOMNode|null
    {
        if (!$node->hasChildNodes()) return $node;

        $childNodes = iterator_to_array($node->childNodes);

        $filtered = array_values(array_filter($childNodes, function ($child) use ($element, $className) {
            if ($child->nodeName === $element) {
                return ($className === '') || self::hasClassName($child, $className);
            }

            return false;
        }));

        // find first match
        return count($filtered) > 0 ? $filtered[0] : null;
    }

    /**
     * Searches to a depth of 3 to find a specified firstElementChild
     *
     * @param DOMNode $node to search
     * @param string $element example: 'p'
     * @param string $className html class name
     * @param int $i current iteration
     *
     * @return DOMNode|null
     */
    private static function recursivelyFindFirstChildElement(DOMNode $node, string $element, string $className, int $i = 1): DOMNode|null
    {
        $maxDepth = 3;

        if (!empty($node->firstElementChild) && $node->firstElementChild->tagName === $element) {
            $found = ($className === '') || self::hasClassName($node->firstElementChild, $className);

            if ($found) return $node->firstElementChild;
        }

        if ($i === $maxDepth || empty($node->firstElementChild)) return null;

        return self::recursivelyFindFirstChildElement($node->firstElementChild, $element, $className, $i + 1);
    }

    /**
     * Recursively searches to a depth of 3 to see if node has a specified child
     *
     * @param DOMNode $node to search
     * @param string $element example: 'p'
     * @param string $className html class name
     *
     * @return bool
     */
    private static function hasDeepChild(DOMNode $node, string $element, string $className): bool
    {
        return !is_null(self::recursivelyFindFirstChildElement($node, $element, $className));
    }

    /**
     * Searches for article by Reference ID in Headlines csv file, gets Canonical URL
     * @param string $referenceId Reference ID of Article
     *
     * @return string url or empty
     */
    private static function getCanonicalURLFromData(string $referenceId): string
    {
        $row = self::findRowByReferenceId($referenceId, self::articleHeadlinesData());
        if (!empty($row)) {
            return $row[self::ARTICLE_URL];
        }

        return '';
    }
}