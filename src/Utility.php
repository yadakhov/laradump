<?php

namespace Yadakhov\Laradump;

class Utility
{
    /**
     * Safe get array by key
     */
    public static function get($array, $key, $default = null)
    {
        if (!isset($array[$key])) {
            return $default;
        }

        return $array[$key];
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string  $haystack
     * @param  string|string[]  $needles
     * @return bool
     */
    public static function endsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string) $needle) {
                return true;
            }
        }

        return false;
    }
}
