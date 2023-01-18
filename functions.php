<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once 'Grammar.php';
require_once 'Production.php';
require_once 'Rule.php';
require_once 'StringHelper.php';

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


function get_tokens_from_grammar_file($metadata)
{
    $array = [];

    foreach ($metadata as $value) {
        if (!StringHelper::contains($value, "::=")) {
            $array[] = $value;
        }
    }

    var_dump($array);

    return $array;
}

function get_grammar_from_grammar_file($metadata)
{
    $array = [];

    foreach ($metadata as $value) {
        if (StringHelper::contains($value, "::=")) {
            $array[] = $value;
        }
    }

    var_dump($array);

    return $array;
}

function read_tokens_from_file(&$grammar, $metadata)
{
}

function read_grammar_from_file(&$grammar, $metadata)
{
    foreach ($metadata as $value) {
        $value = explode("::=", $value);

        // fazer regra para aumentar o nome da regra a partir da gramatica
        $name = StringHelper::regex("/<(.)>/i", $value[0]);

        $rule = new Rule($name);

        $raw_productions = explode("|", $value[1]);

        foreach ($raw_productions as $raw) {
            if (StringHelper::contains($raw, "ε")) {
                // acho que muda um pouco nesse caso, ver exemplo do profe
                $production = new Production();
                $production->setTerminal("ε");
            } else if (!StringHelper::contains($raw, ["<", ">"])) {
                $production = new Production();
                $terminal = trim($raw);
                $production->setTerminal($terminal);
            } else {
                $terminal_before_non_terminal = StringHelper::regex("/(.)</i", $raw);
                $terminal_after_non_terminal = StringHelper::regex("/>(.)/i", $raw);
                $non_terminal = StringHelper::regex("/<(.*?)>/i", $raw);
                $production = new Production();
                $production->setNonTerminal($non_terminal);

                if ($terminal_before_non_terminal != "" && $terminal_after_non_terminal == "") {
                    $production->setTerminal($terminal_before_non_terminal);
                }

                if ($terminal_after_non_terminal != "" && $terminal_before_non_terminal == "") {
                    $production->setTerminal($terminal_after_non_terminal);
                }
            }
            $rule->add_production($production);
        }
        $grammar->add_rule($rule);
    }

    return $grammar;
}
