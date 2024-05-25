<?php
class Utils {
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
}