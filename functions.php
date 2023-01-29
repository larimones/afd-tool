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
    echo(($str)->yellow . ($eol ? PHP_EOL : '') . "\n");
}

function magenta($str, $eol = false)
{
    $c = new Color();
    echo(($str)->magenta . ($eol ? PHP_EOL : '') . "\n");
}

function green($str, $eol = false)
{
    $c = new Color();
    echo(($str)->green . ($eol ? PHP_EOL : '') . "\n");
}

function white($str, $eol = false)
{
    $c = new Color();
    echo(($str)->white . ($eol ? PHP_EOL : '') . "\n");
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

function read_tokens_from_file(&$grammar, $tokens)
{
    $count_of_rules = count($grammar->get_rules());

    foreach ($tokens as $token) {
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

function read_grammar_from_file(&$grammar, $raw_rules)
{
    $count_of_raw_rules = count($raw_rules);
    $should_create_finish_state = false;

    foreach ($raw_rules as $raw_rule) {
        $raw_rule = explode("::=", $raw_rule);

        $name = StringHelper::regex("/<(.)>/i", $raw_rule[0]);

        $is_final = StringHelper::contains($raw_rule[0], "*");

        $rule = ($name == "S") ? $grammar->get_rule_by_name("S") : new Rule($name);

        $rule->set_is_final($is_final);

        $raw_productions = explode("|", $raw_rule[1]);

        foreach ($raw_productions as $raw) {
            if (StringHelper::contains($raw, "ε")) {
                $rule->set_is_final(true);
            } else if (!StringHelper::contains($raw, ["<", ">"])) {
                $production = new Production();
                $terminal = trim($raw);
                $production->set_terminal($terminal);
                $should_create_finish_state = true;
                $non_terminal = StringHelper::convert_number_to_alphabet($count_of_raw_rules + 1);
                $production->set_non_terminal($non_terminal);
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

    if ($should_create_finish_state) {
        $rule = new Rule(StringHelper::convert_number_to_alphabet($count_of_raw_rules + 1), true);
        $grammar->add_rule($rule);
    }

    return $grammar;
}

function print_grammar_in_cmd($grammar)
{
    $terminals = $grammar->get_all_terminals();

    foreach ($grammar->get_rules() as $rule) {
        if ($rule->get_is_final() == true) {
            print("*");
        }
        if ($rule->get_is_initial() == true) {
            print("->");
        }
        if ($rule->is_dead() == true) {
            print("+");
        }
        if (json_encode($rule->get_is_reachable()) == "false") {
            print("+");
        }
        print(" {$rule->get_name()} |");
        foreach ($rule->get_non_terminals_by_terminals($terminals) as $value) {
            print(" " . key($value) . " => { ");
            foreach ($value as $teste) {
                foreach ($teste as $teste1) {
                    print($teste1);
                }
            }
            print(" } ");
        }
        print("\n");
    }
}

function convert_grammar_into_matrix($grammar)
{
    $terminals = $grammar->get_all_terminals();
    $rules = $grammar->get_rules();

    $matrix[0][0] = '$';

    $i = 1;
    foreach ($terminals as $terminal) {
        $matrix[0][$i] = $terminal;
        $i++;
    }

    $i = 1;
    foreach ($rules as $rule) {
        $string = "";

        if (json_encode($rule->get_is_reachable()) == "false") {
            $string .= "o";
        }

        if ($rule->is_dead()) {
            $string .= "+";
        }

        if ($rule->get_is_final()) {
            $string .= "*";
        }

        if ($rule->get_is_initial()) {
            $string .= "->";
        }

        $string = "{$string} {$rule->get_name()}";

        $matrix[$i][0] = $string;

        $transitions = $rule->get_non_terminals_by_terminals($terminals);

        $j = 1;
        foreach ($transitions as $transition) {

            foreach ($transition as $next_rules) {
                $string = "";
                foreach ($next_rules as $next_rule) {
                    $string = "{$string}{$next_rule}";
                }
            }
            $matrix[$i][$j] = $string;
            $j++;
        }
        $i++;
    }

    return $matrix;
}

function print_matrix_into_file($matrix, $file_name, $title)
{
    $fp = fopen("{$file_name}.html", 'w');
    fwrite($fp, "<html><head><meta charset='UTF-8'></head><body>");
    fwrite($fp, "<table style='text-align: center; margin:auto;'><tr><td>Universidade Federal Da Fronteira Sul</td></tr><tr><td>Componente Curricular: Linguagens formais e autômatos</td></tr><tr><td>Professor(a):	Braulio Adriano de Mello</td></tr><tr><td>Acadêmicos(as): Larissa Mones e Matheus Vieira</td></tr><tr><td>Curso: Ciência Da Computação</td></tr></table>");
    fwrite($fp, "<br />");
    fwrite($fp, "<h2 style='text-align: center; margin:auto;'>{$title}</h2>");
    fwrite($fp, "<br />");
    fwrite($fp, "<table border='1' style='text-align: center; margin:auto; border: 1px solid black; border-collapse: collapse;' >");
    foreach ($matrix as $row) {
        fwrite($fp, "<tr>");
        foreach ($row as $col) {
            fwrite($fp, "<td width='100px'>{$col}</td>");
        }
        fwrite($fp, "</tr>");
    }
    fwrite($fp, "</table>");
    fwrite($fp, "<br />");
    fwrite($fp, "<table border='1' style='text-align: center; margin:auto; border: 1px solid black; border-collapse: collapse;' >
    <tr>
        <td colspan='2'>Legenda</td>
    </tr>
    <tr>
        <td width='50px'>-&gt;</td>
        <td width='200px'>Estado Inicial</td>
    </tr>
    <tr>
        <td>*</td>
        <td>Estado Final</td>
    </tr>
    <tr>
        <td>o</td>
        <td>Estado Inalcançável</td>
    </tr>
    <tr>
        <td>+</td>
        <td>Estado Morto</td>
    </tr>
</table>");
    fwrite($fp, "</body></html>");

    fclose($fp);
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

function generate_deterministic_finite_automaton($grammar)
{
    $terminals = $grammar->get_all_terminals();

    $j = 0;
    while (true) {
        $rules = $grammar->get_rules();

        if (!array_key_exists($j, $rules)) {
            break;
        }

        $rule = $rules[$j];

        $non_terminals_by_terminals = $rule->get_non_terminals_by_terminals($terminals);
        foreach ($non_terminals_by_terminals as $non_terminals_by_terminal) {
            $terminal = key($non_terminals_by_terminal);

            $non_terminals = array_values($non_terminals_by_terminal)[0];

            if (count($non_terminals) < 1) {
                continue;
            } else {

                if (count($non_terminals) == 1 and $non_terminals[0] == "-") {
                    continue;
                }

                if (count($non_terminals) == 1 and !StringHelper::contains($non_terminals[0], "[")) {
                    continue;
                }

                $new_rule_name = "[" . join($non_terminals) . "]";

                $verify_rule_existence = $grammar->get_rule_by_name($new_rule_name);
                if ($verify_rule_existence == NULL) {

                    $new_rule = new Rule($new_rule_name);

                    $i = 0;
                    while (true) {
                        if (!array_key_exists($i, $non_terminals)) {
                            break;
                        }

                        $non_terminal = $non_terminals[$i];
                        $reference_rule = $grammar->get_rule_by_name($non_terminal);

                        if ($reference_rule->get_is_final()) {
                            $new_rule->set_is_final(true);
                        }

                        foreach ($reference_rule->get_productions() as $production) {
                            if (StringHelper::contains($production->get_non_terminal(), "[")) {
                                $non_terminals[] = $production->get_non_terminal();
                            } else if ($new_rule->get_production_by_terminal_and_non_terminal($production->get_terminal(), $production->get_non_terminal()) == null) {
                                $new_rule->add_production($production);
                            }
                        }
                        $i++;
                    }

                    $grammar->add_rule($new_rule);
                }
                $new_production = new Production();
                $new_production->set_terminal($terminal);
                $new_production->set_non_terminal($new_rule_name);

                $rule->remove_all_productions_by_terminal($terminal);

                $rule->add_production($new_production);
            }
        }
        $j++;
    }
    set_unreachable_rules($grammar);
}

function set_unreachable_rules($grammar)
{
    $unreachable_rules = $grammar->get_unreachable_rules();

    foreach ($grammar->get_rules() as $rule) {
        if (in_array($rule->get_name(), $unreachable_rules)) {
            $rule->set_is_reachable(false);
        } else {
            $rule->set_is_reachable(true);
        }
    }
}
