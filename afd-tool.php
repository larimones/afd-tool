<?php

require_once 'StringHelper.php';
require_once 'Grammar.php';
require_once 'Production.php';
require_once 'Rule.php';
require_once 'functions.php';
require_once __DIR__ . '/vendor/autoload.php';

use Garden\Cli\Cli;

try {
    error_reporting(E_ERROR | E_PARSE);

    $cli = new Cli();

    $cli->description('Implementa a conversão de GRs em AFDs')
        ->opt('grammar:g', 'Caminho para o arquivo com a GR.');

    $args = $cli->parse($argv, true);

    $ds = DIRECTORY_SEPARATOR;
    $grammar_path = $args->getOpt('gr', __DIR__ . $ds . 'grammar');

    $metadata = get_and_validate_grammar_file($grammar_path);

    $grammar_from_tokens = new Grammar();

    read_tokens_from_file($grammar_from_tokens, get_tokens_from_grammar_file($metadata));

    $grammar_from_file = new Grammar();

    read_grammar_from_file($grammar_from_file, get_grammar_from_grammar_file($metadata));

    $grammar = unify_grammars($grammar_from_tokens, $grammar_from_file);

    //print_nondeterministic_finite_automaton($grammar);

    fopen("teste.html", "w");
    echo ("<table border='1'><thead><td>sigma</td>\n");

    $testeeee = $grammar->get_all_terminals();
    sort($testeeee);
    foreach ($testeeee as $terminal) {
        echo ("<th>{$terminal}</th>\n");
    }
    echo ("</thead><tbody>\n");
    foreach ($grammar->get_rules() as $rule) {
        echo ("<tr><th>{$rule->get_name()}</th>\n");
        $rules = $rule->get_non_terminals_by_terminals();
        $rules[] = $testeeee;
        array_unique($rules);
        foreach ($rules as $value) {
            echo ("<td>");
            foreach ($testeeee as $terminal) {
                if (key($value) == $terminal) {
                    foreach ($value as $teste) {
                        foreach ($teste as $teste1) {
                            if ($teste[count($teste)] == $teste1) {
                                echo ("{$teste1}");
                            } else {
                                echo ("{$teste1},");
                            }
                        }
                    }
                }
            }
            echo ("</td>");
        }
        echo ("</tr>\n");
    }
    echo ("</tbody></table>\n");
} catch (Exception $e) {
}
