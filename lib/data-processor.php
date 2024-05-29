<?php

class DataProcessor
{
    /**
     * Finds all headline keywords in Headlines file, filtered by occurrence count
     * @return array keywords => count
     */
    static function allKeywordsFiltered(): array
    {
        $headlines = array_column(APNewsScraper::articleHeadlinesData(), APNewsScraper::ARTICLE_HEADER);
        $keywords = Utils::countAllKeywords($headlines);
        return Utils::filterByCount($keywords);
    }
    
    /**
     * Formats all headline keywords into chart.js data structure
     * @return array
     */
    static function keywordData(): array
    {
        return Utils::keywordsToBarChartData(self::allKeywordsFiltered(), "Headline Keywords\n(Filtered > 2 Occurrences)");
    }
    
    /**
     * Formats all headline keywords into chart.js data structure JSON
     * @return string
     */
    static function keywordDataJson(): string
    {
        return Utils::keywordsToBarChartJson(self::allKeywordsFiltered(), "Headline Keywords\n(Filtered > 2 Occurrences)");
    }
    
    /**
     * Finds all headlines and groups them by keyword, filtered by occurrence count
     * @return array keywords => article sum
     */
    static function articlesByKeywords(): array
    {
        $headlines = array_column(APNewsScraper::articleHeadlinesData(), APNewsScraper::ARTICLE_HEADER);
        $articlesByKeywords = Utils::articleSumByKeyword($headlines);
        return Utils::filterByCount($articlesByKeywords);
    }
    
    /**
     * Finds all headlines and gathers sum of headline length
     * @return array category => count
     */
    static function headlineLengths(): array
    {
        $headlines = array_column(APNewsScraper::articleHeadlinesData(), APNewsScraper::ARTICLE_HEADER);
        return Utils::headlineLengthAnalysis($headlines);
    }

    /**
     * Formats headlineLengths data into chart.js data structure
     * @return array
     */
    static function headlineLengthsData(): array
    {
        return Utils::headlineLengthsToPieChartData(self::headlineLengths(), "Article Headline Lengths");
    }

    /**
     * Formats headlineLengths data into chart.js data structure JSON
     */
    static function headlineLengthsJson(): string
    {
        return Utils::headlineLengthsToPieChartJson(self::headlineLengths(), "Article Headline Lengths");
    }

    /**
     * Formats articlesByKeywords data into chart.js data structure
     * @return array
     */
    static function articlesByKeywordsData(): array
    {
        return Utils::keywordsToBarChartData(self::articlesByKeywords(), "Article Headlines per Keyword\n(Filtered > 2 Occurrences)");
    }
    
/**
     * Formats articlesByKeywords data into chart.js data structure JSON
     * @return string
     */
    static function articlesByKeywordsDataJson(): string
    {
        return Utils::keywordsToBarChartJson(self::articlesByKeywords(), "Article Headlines per Keyword\n(Filtered > 2 Occurrences)");
    }

    /**
     * Finds keyword occurrences within a specific article body
     * @param string $body Article Body
     *
     * @return array keywords => count
     */
    static function articleInsightsKeywords(string $body): array
    {
        $keywordSums = Utils::articleInsightSumKeywords($body);
        return Utils::filterByCount($keywordSums, 5);
    }

    /**
     * Formats articleInsightsKeywords data into chart.js data structure
     * @return array
     */
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