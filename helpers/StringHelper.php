<?php

namespace Helpers;

use Exception;

class StringHelper
{
    /**
     * Returns a string based on the expression given
     * @param string $expression
     * @param string $string
     * @param int $position
     * @return null|string
     */

    public static function regex(string $expression, string $string, int $position = 1): ?string
    {
        preg_match($expression, $string, $result);

        return isset($result[$position]) ? trim($result[$position]) : null;
    }

    /**
     * Returns if a string is present into another
     * @param mixed $haystack
     * @param mixed $needles
     * @return bool
     */

    public static function contains(mixed $haystack, mixed $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== '' && mb_stripos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $number
     * @return string
     * @throws Exception
     */
    public static function convert_number_to_alphabet(int $number): string
    {
        if ($number < 0) {
            throw new Exception("Error: number cannot be converted");
        }

        $var = "";

        if ($number > 26)
            $check = $number % 26;
        else
            $check = $number;

        for ($i = 0; $i <= $number; $i = $i + 27) {
            switch ($check) {
                case 1:
                    $var = "{$var}S";
                    break;
                case 2:
                    $var = "{$var}A";
                    break;
                case 3:
                    $var = "{$var}B";
                    break;
                case 4:
                    $var = "{$var}C";
                    break;
                case 5:
                    $var = "{$var}D";
                    break;
                case 6:
                    $var = "{$var}E";
                    break;
                case 7:
                    $var = "{$var}F";
                    break;
                case 8:
                    $var = "{$var}G";
                    break;
                case 9:
                    $var = "{$var}H";
                    break;
                case 10:
                    $var = "{$var}I";
                    break;
                case 11:
                    $var = "{$var}J";
                    break;
                case 12:
                    $var = "{$var}K";
                    break;
                case 13:
                    $var = "{$var}L";
                    break;
                case 14:
                    $var = "{$var}M";
                    break;
                case 15:
                    $var = "{$var}N";
                    break;
                case 16:
                    $var = "{$var}O";
                    break;
                case 17:
                    $var = "{$var}P";
                    break;
                case 18:
                    $var = "{$var}Q";
                    break;
                case 19:
                    $var = "{$var}R";
                    break;
                case 20:
                    $var = "{$var}T";
                    break;
                case 21:
                    $var = "{$var}U";
                    break;
                case 22:
                    $var = "{$var}V";
                    break;
                case 23:
                    $var = "{$var}W";
                    break;
                case 24:
                    $var = "{$var}X";
                    break;
                case 25:
                    $var = "{$var}Y";
                    break;
                case 0:
                    $var = "{$var}Z";
                    break;
            }
        }

        return $var;
    }
}
