<?php

class StringHelper
{
    /**
     * Returns a string based on the expression given
     * @param $string
     * @param $string
     * @param $int
     * @return null|string
     */

    public static function regex($expression, $string, $position = 1)
    {
        preg_match($expression, $string, $result);

        return isset($result[$position]) ? trim($result[$position]) : null;
    }

    /**
     * Returns if a string is present into another
     * @param $string
     * @param $string
     * @return bool
     */

    public static function contains($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== '' && mb_stripos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}