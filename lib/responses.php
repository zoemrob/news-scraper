<?php

class Responses
{
    static function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }
    
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