<?php

class DataProcessor
{
    static function allKeywordsFiltered(): array
    {
        $headlines = array_column(APNewsScraper::articleHeadlinesData(), APNewsScraper::ARTICLE_HEADER);
        $keywords = Utils::countAllKeywords($headlines);
        return Utils::filterByCount($keywords);
    }
    
    static function keywordData(): array
    {
        return Utils::keywordsToBarChartData(self::allKeywordsFiltered(), "Headline Keywords\n(Filtered > 2 Occurrences)");
    }
    
    static function keywordDataJson(): string
    {
        return Utils::keywordsToBarChartJson(self::allKeywordsFiltered(), "Headline Keywords\n(Filtered > 2 Occurrences)");
    }
    
    static function articlesByKeywords(): array
    {
        $headlines = array_column(APNewsScraper::articleHeadlinesData(), APNewsScraper::ARTICLE_HEADER);
        $articlesByKeywords = Utils::articleSumByKeyword($headlines);
        return Utils::filterByCount($articlesByKeywords);
    }
    
    static function articlesByKeywordsData(): array
    {
        return Utils::keywordsToBarChartData(self::articlesByKeywords(), "Article Headlines per Keyword\n(Filtered > 2 Occurrences)");
    }
    
    static function articlesByKeywordsDataJson(): string
    {
        return Utils::keywordsToBarChartJson(self::articlesByKeywords(), "Article Headlines per Keyword\n(Filtered > 2 Occurrences)");
    }

    static function articleInsightsKeywords(string $body): array
    {
        $keywordSums = Utils::articleInsightSumKeywords($body);
        return Utils::filterByCount($keywordSums, 5);
    }

    static function articleInsightsKeywordsData(array $articleInsights): array
    {
        $filtered = self::articleInsightsKeywords($articleInsights[APNewsScraper::ARTICLE_BODY]);
        if (empty($filtered)) return [];

        return Utils::keywordsToBarChartData(
            $filtered,
            "Selected Article Keywords\n(Filtered > 5 Occurrences)"
        );
    }
}