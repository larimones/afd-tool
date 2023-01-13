<?php

require_once __DIR__ . '/vendor/autoload.php';

use Colors\Color;

function yellow($str, $eol = false)
{
    $c = new Color();
    echo $c($str)->yellow . ($eol ? PHP_EOL : '') . "\n";
}

function magenta($str, $eol = false)
{
    $c = new Color();
    echo $c($str)->magenta . ($eol ? PHP_EOL : '') . "\n";
}

function green($str, $eol = false)
{
    $c = new Color();
    echo $c($str)->green . ($eol ? PHP_EOL : '') . "\n";
}

function white($str, $eol = false)
{
    $c = new Color();
    echo $c($str)->white . ($eol ? PHP_EOL : '') . "\n";
}

function get_and_validate_grammar_file($path)
{
    @$file = file_get_contents($path);

    if ($file === false) {
        magenta("Erro ao ler arquivo informado em --grammar: '$path'");
        exit(1);
    }

    return array_filter(explode("\n", $file));
}