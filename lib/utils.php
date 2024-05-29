<?php

class Utils {
    // Crude list of stop words to filter out noise. Not comprehensive.
    // Likely a library would be more suitable in a production environment.
    const STOP_WORDS = [
        '-',
        'at',
        's',
        'as',
        'what',
        'are',
        'and',
        'the',
        'is',
        'a',
        'an',
        'this',
        'in',
        'it',
        'its',
        'on',
        'or',
        'of',
        'to',
        'was',
        'with',
        'for',
        'that',
        'some',
        'from',
    ];

    // Thought I would be clever and steal some OSINT Combine brand colors
    const OSINT_BLUE = 'blue';
    const OSINT_BLUE_LIGHT = 'blue-light';
    const OSTINT_ORANGE = 'orange';

    const OSINT_BRAND_COLORS = [
        self::OSTINT_ORANGE => 'rgb(242, 144, 65)',
        self::OSINT_BLUE => 'rgba(40, 37, 96, 1)',
        self::OSINT_BLUE_LIGHT => 'rgba(40, 37, 96, .1)',
    ];

    const SHORT_HEADLINE = 'Short (< 40 chars)';
    const MED_HEADLINE = 'Medium (<= 80 chars)';
    const LONG_HEADLINE = 'Long (80+ chars)';

    /**
     * Vastly simplifies/normalizes request URI
     * @param string $path full Request URI
     *
     * @return string normalized URI path
     */
    static function normalizePath(string $path): string
    {
        $path = preg_replace('/[\\\\\/]+/', '/', parse_url($path, PHP_URL_PATH));
        $segments = explode('/', trim($path, '/'));
        $ret = [];
        foreach ($segments as $segment) {
            if ($segment === '..') {
                array_pop($ret);
            } elseif ($segment !== '.') {
                $ret[] = $segment;
            }
        }
        return '/' . implode('/', $ret);
    }

    /**
     * Helper method to group keywords by their sum, across all supplied strings and sort result
     * @param array $strings strings to extract keywords from
     * @param int $sortDirection for multisort
     *
     * @return array keyword => sum
     */
    static function countAllKeywords(array $strings, int $sortDirection = SORT_DESC): array
    {
        $counts = [];

        foreach ($strings as $string) {
            $normalized = preg_replace('/\d\']/', '', strtolower($string));
            $words = explode(' ', $normalized);

            // Count the words
            foreach ($words as $word) {
                if ($word === '' || in_array($word, self::STOP_WORDS)) continue;

                // Increment the count for the word, or initialize it to 1 if it doesn't exist
                if (isset($counts[$word])) {
                    $counts[$word]++;
                } else {
                    $counts[$word] = 1;
                }
            }
        }

        array_multisort($counts, $sortDirection);
        return $counts;
    }

    /**
     * Helper method to aggregate the number of headlines where a given keyword occurs and sort result
     * @param array $headlines headlines to check for keywords
     * @param int $sortDirection for multisort
     *
     * @return array keyword => sum of article headlines
     */
    static function articleSumByKeyword(array $headlines, int $sortDirection = SORT_DESC): array
    {
        $articlesByKeywords = [];

        foreach($headlines as $headline) {
            $normalized = preg_replace('/\d\']/', '', strtolower($headline));
            $words = explode(' ', $normalized);

            foreach ($words as $word) {
                if ($word === '' || in_array($word, self::STOP_WORDS)) continue;

                if (!isset($articlesByKeywords[$word])) $articlesByKeywords[$word] = [];

                if (!in_array($headline, $articlesByKeywords[$word])) $articlesByKeywords[$word][] = $headline;
            }
        }

        foreach ($articlesByKeywords as $keyword => $headlines) {
            $articlesByKeywords[$keyword] = count($headlines);
        }

        array_multisort($articlesByKeywords, $sortDirection);
        return $articlesByKeywords;
    }

    /**
     * Performs analysis on headline length and categorizes as short, medium, or long
     * @param array $headlines Article Headlines
     *
     * @return array category => count
     */
    static function headlineLengthAnalysis(array $headlines): array
    {
        $categories = [
            self::SHORT_HEADLINE => 0,
            self::MED_HEADLINE => 0,
            self::LONG_HEADLINE => 0,
        ];

        foreach ($headlines as $headline) {
            $length = strlen($headline);

            if ($length < 40) {
                $categories[self::SHORT_HEADLINE]++;
            } elseif ($length <= 80) {
                $categories[self::MED_HEADLINE]++;
            } else {
                $categories[self::LONG_HEADLINE]++;
            }
        }

        return $categories;
    }

    /**
     * Filters an array by a count threshold
     * @param array $counts key => int
     * @param int $count minimum threshold (non-inclusive)
     *
     * @return array key => int
     */
    static function filterByCount(array $counts, int $count = 2): array
    {
        return array_filter($counts, fn($value) => $value > $count);
    }

    /**
     * Count keywords of a string, group by word, and sort
     * @param string $body string to search for keywords
     * @param int $sortDirection for multisort
     *
     * @return array keyword => int
     */
    static function articleInsightSumKeywords(string $body, int $sortDirection = SORT_DESC): array
    {
        $normalized = strtolower($body);

        $keywords = [];
        foreach(explode(' ', $normalized) as $word) {
            if ($word === '' || in_array($word, self::STOP_WORDS)) continue;

            if (!isset($keywords[$word])) $keywords[$word] = 0;

            $keywords[$word]++;
        }

        array_multisort($keywords, $sortDirection);
        return $keywords;
    }

    /**
     * Converts an array of string => int into chart.js format
     * @param array $keywords string => int
     * @param string $label Label for chart legend
     *
     * @return array
     */
    static function keywordsToBarChartData(array $keywords, string $label): array
    {
        return [
            'type' => 'bar',
            'data' => [
                'labels' => array_keys($keywords),
                'datasets' => [
                    [
                        'data' => array_values($keywords),
                        'label' => $label,
                        'backgroundColor' => self::OSINT_BRAND_COLORS[self::OSTINT_ORANGE],
                        'hoverBackgroundColor' => self::OSINT_BRAND_COLORS[self::OSINT_BLUE],
                    ],
                ],
            ],
            'options' => [
                'plugins' => [
                    'legend' => [
                        'labels' => [
                            'font' => [
                                'size' => 16
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Converts an array of string => int into chart.js pie format
     * @param array $lengths string => int
     * @param string $label Label for chart legend
     *
     * @return array
     */
    static function headlineLengthsToPieChartData(array $lengths, string $label): array
    {
        return [
            'type' => 'pie',
            'data' => [
                'labels' => array_keys($lengths),
                'datasets' => [
                    [
                        'data' => array_values($lengths),
                        'label' => $label,
                        'backgroundColor' => array_reverse(array_values(self::OSINT_BRAND_COLORS)),
                    ],
                ],
            ],
            'options' => [
                'plugins' => [
                    'legend' => [
                        'labels' => [
                            'font' => [
                                'size' => 16
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Variant of headlineLengthsToPieChartData which returns chart.js data as JSON
     * @param array $lengths string => int
     * @param string $label Label for chart legend
     *
     * @return array
     */
    static function headlineLengthsToPieChartJson(array $lengths, string $label): string
    {
        return json_encode(self::headlineLengthsToPieChartData($lengths, $label));
    }

    /**
     * Variant of keywordsToBarChart which returns as JSON
     * @param array $keywords string => int
     * @param string $label Label for chart legend
     *
     * @return string
     */
    static function keywordsToBarChartJson(array $keywords, string $label): string
    {
        return json_encode(self::keywordsToBarChartData($keywords, $label));
    }
}