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

    public static function convert_number_to_alphabet(int $number): string
    {
        if ($number > 27 || $number < 0) {
            throw new Exception("Error: number cannot be converted");
        }

        switch ($number) {
            case 1:
                return 'S';
            case 2:
                return 'A';
            case 3:
                return 'B';
            case 4:
                return 'C';
            case 5:
                return 'D';
            case 6:
                return 'E';
            case 7:
                return 'F';
            case 8:
                return 'G';
            case 9:
                return 'H';
            case 10:
                return 'I';
            case 11:
                return 'J';
            case 12:
                return 'K';
            case 13:
                return 'L';
            case 14:
                return 'M';
            case 15:
                return 'N';
            case 16:
                return 'O';
            case 17:
                return 'P';
            case 18:
                return 'Q';
            case 19:
                return 'R';
            case 20:
                return 'S';
            case 21:
                return 'T';
            case 22:
                return 'U';
            case 23:
                return 'V';
            case 24:
                return 'W';
            case 25:
                return 'X';
            case 26:
                return 'Y';
            case 27:
                return 'Z';
        }
    }
}
