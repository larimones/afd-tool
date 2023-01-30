<?php

namespace Helpers;

use Colors\Color;

class CommandLineHelper
{
    /**
     * @param string $str
     * @param bool $eol
     * @return void
     */
    public static function print_yellow_message(string $str, bool $eol = false): void
    {
        $c = new Color($str);
        echo($c->yellow . ($eol ? PHP_EOL : '') . "\n");
    }

    /**
     * @param string $str
     * @param bool $eol
     * @return void
     */
    public static function print_magenta_message(string $str, bool $eol = false): void
    {
        $c = new Color($str);
        echo($c->magenta . ($eol ? PHP_EOL : '') . "\n");
    }

    /**
     * @param string $str
     * @param bool $eol
     * @return void
     */
    public static function print_green_message(string $str, bool $eol = false): void
    {
        $c = new Color($str);
        echo($c->green . ($eol ? PHP_EOL : '') . "\n");
    }

    /**
     * @param string $str
     * @param bool $eol
     * @return void
     */
    public static function print_white_message(string $str, bool $eol = false): void
    {
        $c = new Color($str);
        echo($c->white . ($eol ? PHP_EOL : '') . "\n");
    }
}