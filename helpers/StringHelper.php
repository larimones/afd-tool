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
		if ($number < 1) {
			throw new Exception("Error: number cannot be negative");
		}

		$alphabet = range('A', 'Z');

		$base = count($alphabet);
		$result = '';

		while ($number > 0) {
			$index = ($number - 1) % $base;
			$result = $alphabet[$index] . $result;
			$number = (int) (($number - 1) / $base);
		}

		return $result;
	}
}
