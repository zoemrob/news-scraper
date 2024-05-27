<?php 

/**
 * Scoped functions and constants pertaining to scraping AP News
 */
class APNewsScraper
{
    const TOP_URL = 'https://www.apnews.com';
    const CSV_FILE_NAME = 'apnewsdata-articles.csv';

    const ARTICLE_HEADER = 'Article Header';
    const ARTICLE_URL = 'Article URL';
    const ARTICLE_DESCRIPTION = 'Article Description';
    const ARTICLE_REFERENCE_ID = 'Reference ID';

    const CSV_HEADERS = [
        self::ARTICLE_HEADER,
        self::ARTICLE_URL,
        self::ARTICLE_DESCRIPTION,
        self::ARTICLE_REFERENCE_ID,
    ];
    
    // XPATH Selectors
    const ARTICLE_HEADERS_SELECTOR = './/h2[contains(@class,"PagePromo-title")]|.//h3[contains(@class, "PagePromo-title")]';

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

    static function scrapeArticleData(): array
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

    static function saveArticleDataToCSV(string $filename, array $content)
    {
        $file = fopen($_SERVER['DOCUMENT_ROOT'] . '/' . $filename, 'w+');
        fputcsv($file, self::CSV_HEADERS);

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

    static function hasData(): bool
    {
        return file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . self::CSV_FILE_NAME);
    }

    static function articleData(): array
    {
        if (!self::hasData()) return [];

        $csv = fopen($_SERVER['DOCUMENT_ROOT'] . '/' . self::CSV_FILE_NAME, 'r');
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
        if ($node->parentElement->nodeName === $element) {
            $found = empty($className) || self::hasClassName($node->parentElement, $className);

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
                return empty($className) || self::hasClassName($child, $className);
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
            $found = empty($className) || self::hasClassName($node->firstElementChild, $className);

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
}