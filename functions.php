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
                $production->set_terminal($token_as_array[$i]);
                $production->set_non_terminal(StringHelper::convert_number_to_alphabet($count_of_rules + 1));

                $rule->add_production($production);
            } else {
                $count_of_rules++;
                $rule = new Rule(StringHelper::convert_number_to_alphabet($count_of_rules));

                $production = new Production();
                $production->set_terminal($token_as_array[$i]);
                $production->set_non_terminal(StringHelper::convert_number_to_alphabet($count_of_rules + 1));

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
    foreach ($metadata as $value) {
        $value = explode("::=", $value);

        $name = StringHelper::regex("/<(.)>/i", $value[0]);

        $rule = ($name == "S") ? $grammar->get_rule_by_name("S") : new Rule($name);

        $raw_productions = explode("|", $value[1]);

        foreach ($raw_productions as $raw) {
            if (StringHelper::contains($raw, "ε")) {
                $rule->set_is_final(true);
            } else if (!StringHelper::contains($raw, ["<", ">"])) {
                $production = new Production();
                $terminal = trim($raw);
                $production->set_terminal($terminal);
            } else {
                // retirar parte de pegar da esquerda o nao terminal
                $terminal_before_non_terminal = StringHelper::regex("/(.)</i", $raw);
                $terminal_after_non_terminal = StringHelper::regex("/>(.)/i", $raw);
                $non_terminal = StringHelper::regex("/<(.*?)>/i", $raw);
                $production = new Production();
                $production->set_non_terminal($non_terminal);

                if ($terminal_before_non_terminal != "" && $terminal_after_non_terminal == "") {
                    $production->set_terminal($terminal_before_non_terminal);
                }

                if ($terminal_after_non_terminal != "" && $terminal_before_non_terminal == "") {
                    $production->set_terminal($terminal_after_non_terminal);
                }
            }
            $rule->add_production($production);
        }

        if ($rule->get_name() != "S")
            $grammar->add_rule($rule);
    }

    return $grammar;
}


function print_nondeterministic_finite_automaton($grammar)
{
    //todo: these are all tests for the afnd table, it needs a refact asap

    foreach ($grammar->get_rules() as $rule) {
        if ($rule->get_is_final() == true) {
            print("*");
        }
        if ($rule->get_is_initial() == true) {
            print("->");
        }
        print(" {$rule->get_name()} |");
        foreach ($rule->get_non_terminals_by_terminals() as $value) {
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

function unify_grammars($grammar1, $grammar2)
{
    $count_of_rules = count($grammar1->get_rules());
    $rules_names = [];

    foreach ($grammar2->get_rules() as $rule) {
        if ($rule->get_name() == "S") {
            continue;
        }

        $count_of_rules++;
        array_push($rules_names, [
            "{$rule->get_name()}" => $count_of_rules
        ]);
    }

    foreach ($rules_names as $rule_name) {
        foreach ($grammar2->get_rules() as $rule) {
            if ($rule->get_name() == key($rule_name)) {
                $rule->set_name(StringHelper::convert_number_to_alphabet($rule_name[key($rule_name)]));
            }
            foreach ($rule->get_productions() as $production) {
                if ($production->get_non_terminal() == key($rule_name)) {
                    $production->set_non_terminal(StringHelper::convert_number_to_alphabet($rule_name[key($rule_name)]));
                }
            }
        }
    }

    foreach ($grammar2->get_rules() as $rule) {
        if ($rule->get_name() == "S") {
            $ruleSGrammar1 = $grammar1->get_rule_by_name("S");

            foreach ($rule->get_productions() as $production) {
                $ruleSGrammar1->add_production($production);
            }
        } else {
            $grammar1->add_rule($rule);
        }
    }

    return $grammar1;
}
