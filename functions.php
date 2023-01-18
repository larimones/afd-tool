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

    return $array;
}

// refact pls

function read_tokens_from_file(&$grammar, $metadata)
{
    $count_of_rules = count($grammar->get_rules());

    foreach ($metadata as $token) {
        $token = trim($token);
        $token_as_array = array_filter(str_split($token));

        for ($i = 0; $i < count($token_as_array); $i++) {
            if ($i == 0) {
                $rule = $grammar->get_rule_by_name("S");

                $production = new Production();
                $production->setTerminal($token_as_array[$i]);
                $production->setNonTerminal(StringHelper::convert_number_to_alphabet($count_of_rules + 1));

                $rule->add_production($production);
            } else {
                $count_of_rules++;
                $rule = new Rule(StringHelper::convert_number_to_alphabet($count_of_rules));

                $production = new Production();
                $production->setTerminal($token_as_array[$i]);
                $production->setNonTerminal(StringHelper::convert_number_to_alphabet($count_of_rules + 1));

                $rule->add_production($production);
                $grammar->add_rule($rule);
            }
        }

        $count_of_rules++;
        $rule = new Rule(StringHelper::convert_number_to_alphabet($count_of_rules), true);
        $grammar->add_rule($rule);
    }
}

function read_grammar_from_file(&$grammar, $metadata)
{
    $initial_rule =  $grammar->get_rule_by_name("S");
    $count_of_rules = count($grammar->get_rules());

    $value = explode("::=", $metadata[0]);

    $raw_productions = explode("|", $value[1]);
    $count_of_rules++;

    foreach ($raw_productions as $raw) {
        $production = read_and_create_production_from_file($raw, $count_of_rules);
        $initial_rule->add_production($production);
    }

    for ($i = 1; $i < count($metadata); $i++) {
        $value = $metadata[$i];
        $value = explode("::=", $value);

        $count_of_rules++;
        $rule = new Rule(StringHelper::convert_number_to_alphabet($count_of_rules));

        $raw_productions = explode("|", $value[1]);

        foreach ($raw_productions as $raw) {
            $production = read_and_create_production_from_file($raw, $count_of_rules + 1);
            $rule->add_production($production);
        }
        $grammar->add_rule($rule);
    }

    return $grammar;
}

function read_and_create_production_from_file($raw, $next_rule)
{
    if (StringHelper::contains($raw, "ε")) {
        // acho que muda um pouco nesse caso, ver exemplo do profe
        $production = new Production();
        $production->setTerminal("ε");
    } else if (!StringHelper::contains($raw, ["<", ">"])) {
        $production = new Production();
        $terminal = trim($raw);
        $production->setTerminal($terminal);
    } else {
        // retirar parte de pegar da esquerda o nao terminal
        $terminal_before_non_terminal = StringHelper::regex("/(.)</i", $raw);
        $terminal_after_non_terminal = StringHelper::regex("/>(.)/i", $raw);
        $non_terminal = StringHelper::regex("/<(.*?)>/i", $raw);
        $production = new Production();
        $production->setNonTerminal(StringHelper::convert_number_to_alphabet($next_rule));

        if ($terminal_before_non_terminal != "" && $terminal_after_non_terminal == "") {
            $production->setTerminal($terminal_before_non_terminal);
        }

        if ($terminal_after_non_terminal != "" && $terminal_before_non_terminal == "") {
            $production->setTerminal($terminal_after_non_terminal);
        }
    }

    return $production;
}

function print_nondeterministic_finite_automaton($grammar)
{
    //todo: these are all tests for the afnd table, it needs a refact asap

    foreach ($grammar->get_rules() as $rule) {
        if ($rule->get_is_final() == true) {
            print("*");
        }
        print(" {$rule->getName()} |");
        foreach ($rule->getNonTerminalsByTerminals() as $value) {
            print(" " . key($value) . " => { ");
            foreach ($value as $teste) {
                foreach ($teste as $teste1) {
                    print($teste1);
                }
            }
            print(" },");
        }
        print("\n");
    }
}
