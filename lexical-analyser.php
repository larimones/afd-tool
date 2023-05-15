<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/dependency-injection.php';

use Configuration\Configuration;
use Entities\Grammar;
use Garden\Cli\Cli;
use Helpers\CommandLineHelper;

try {

    $input_file_service = $containerBuilder->get('InputFileService');
    $grammar_mapper = $containerBuilder->get('GrammarMapper');
    $print_service = $containerBuilder->get('PrintService');
    $finite_automaton_service = $containerBuilder->get('FiniteAutomatonService');

    CommandLineHelper::print_yellow_message("Welcome to Matheus and Larissa`s Lexical Analyser\n");

    error_reporting(E_ERROR | E_PARSE);

    $cli = new Cli();

    $cli->description('Implementa o analisador léxico da linguagem Laritheus')
        ->opt('grammar:g', 'Caminho para o arquivo com a GR.')
        ->opt('code:c', 'Caminho para o arquivo com o código a ser analisado.');

    $args = $cli->parse($argv, true);

    $ds = DIRECTORY_SEPARATOR;
    $grammar_path = $args->getOpt('g', __DIR__ . $ds . 'grammar');
    $code_path = $args->getOpt('c', __DIR__ . $ds . 'code');

    // Extract to a service
    CommandLineHelper::print_white_message("Reading instructions from file");

    $metadata = $input_file_service->get_and_validate_file_content($grammar_path);

    $tokens = $input_file_service->get_tokens_from_grammar_file($metadata);
    if (count($tokens) > 0) {
        $grammar_from_tokens = new Grammar();
        $grammar_mapper->from_tokens($grammar_from_tokens, $tokens);
        CommandLineHelper::print_green_message("Successfully processed tokens from file");
    }

    $grammar_from_file_as_array = $input_file_service->get_grammar_from_grammar_file($metadata);
    if (count($grammar_from_file_as_array) > 0) {
        $grammar_from_file = new Grammar();
        $grammar_mapper->from_bnf_regular_grammar($grammar_from_file, $grammar_from_file_as_array);
        CommandLineHelper::print_green_message("Successfully processed BNF grammar from file");
    }

    $grammar = $grammar_mapper->unify_grammars($grammar_from_tokens, $grammar_from_file);
    $finite_automaton_service->transform_grammar_in_deterministic_finite_automaton($grammar);

    $code_lines = $input_file_service->get_and_validate_file_content($code_path);

    $fp1 = fopen('fita.csv', 'w');
    $fita = [];
    $fp2 = fopen('tabela_simbolos.csv', 'w');
    $tabela_simbolos = [];

    foreach ($code_lines as $code_line){
        //faço o algoritmo da aula que é uma loucura
        // é nesse sentido

        $tokens_in_line = explode(" ", $code_line);

        foreach ($tokens_in_line as $token){

            $rule = $grammar->get_init_rule();

            $i = 0;

            do {
                $next_rule = $rule->get_non_terminal_by_terminal($token[$i]);

                $rule = ($next_rule != null) ? $grammar->get_rule_by_name($next_rule) : $rule;
                $i++;
            } while ($i < strlen($token));

            if ($rule->get_is_final()) {
                fputcsv($fp1, [$rule->get_name()]);
                array_push($fita, $rule->get_name());
                fputcsv($fp2, [0, $token, 0]);
                array_push($tabela_simbolos, [0, $token, 0]);
            }
            else {
                fputcsv($fp1, [$rule->get_name()]);
                array_push($fita, Configuration::get_err_rule_name());
            }
        }
    }

    //var_dump($fita);
    //var_dump($tabela_simbolos);

} catch (Exception $e) {
    CommandLineHelper::print_magenta_message("Oops, we found an error while processing your request, please contact our development team to solve it.");
    $fp = fopen("error_log", 'a+');
    fwrite($fp, $e->getMessage());
    fclose($fp);
}
