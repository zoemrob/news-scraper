<?php

class Responses
{
    /**
     * Helper method that sends json header and json response
     */
    static function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }
    
    /**
     * Sends back json data for /insights route
     */
    static function sendInsights(): void
    {
        ob_start();
        require_once $_SERVER['DOCUMENT_ROOT'] . '/views/_articles_table.php';
        $tableHtml = ob_get_clean();
        
        self::jsonResponse([
            'chartLeft' => DataProcessor::keywordData(),
            'chartRight' => DataProcessor::articlesByKeywordsData(),
            'tableHtml' => $tableHtml,
        ]);
    }

    /**
     * Sends back json data for /insights/article/:referenceId route
     */
    static function sendArticleInsight(array $articleData): void
    {
        $formatted = DataProcessor::articleInsightsKeywordsData($articleData);
        if (empty($formatted)) {
            self::jsonResponse(['chartRight' => 'No keywords occurring greater than 5 times for the article.']);
        } else {
            self::jsonResponse(['chartRight' => $formatted]);
        }
    }
}