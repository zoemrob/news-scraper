<?php
class Utils {
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
    ];

    static function normalizePath(string $path): string
    {
        $path = preg_replace('/[\\\\\/]+/', '/', $path);
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

    static function countAllKeywords(array $strings, $sortDirection = SORT_DESC): array
    {
        $counts = [];

        foreach ($strings as $string) {
            $normalized = strtolower($string);
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

    static function articleSumByKeyword(array $headlines, $sortDirection = SORT_DESC): array
    {
        $articlesByKeywords = [];

        foreach($headlines as $headline) {
            $normalized = strtolower($headline);
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

    static function filterByCount(array $counts, int $count = 2): array
    {
        return array_filter($counts, fn($value) => $value > $count);
    }

    static function keywordsToBarChartJson(array $keywords, string $label): string
    {
        return json_encode([
            'type' => 'bar',
            'data' => [
                'labels' => array_keys($keywords),
                'datasets' => [
                    [
                        'data' => array_values($keywords),
                        'label' => $label,
                    ],
                ],
            ],
        ]);
    }
}