<?php

class DataProcessor
{
    static function allKeywordsFiltered(): array
    {
        $headlines = array_column(APNewsScraper::articleData(), APNewsScraper::ARTICLE_HEADER);
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
        $headlines = array_column(APNewsScraper::articleData(), APNewsScraper::ARTICLE_HEADER);
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
}