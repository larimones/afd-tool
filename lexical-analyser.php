<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/dependency-injection.php';

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

    $code_lines = $input_file_service->get_and_validate_file_content($grammar_path);

    foreach ($code_lines as $code_line){
        //faço o algoritmo da aula que é uma loucura
        // é nesse sentido
        $rule = $grammar->get_init_rule();

        $next_rule = $rule->get_non_terminal_by_terminal();

        while ($next_rule != null || ){
            $rule = $grammar->get_rule_by_name($next_rule);

            $next_rule = $rule->get_non_terminal_by_terminal();
        }
    }


} catch (Exception $e) {
    CommandLineHelper::print_magenta_message("Oops, we found an error while processing your request, please contact our development team to solve it.");
    $fp = fopen("error_log", 'a+');
    fwrite($fp, $e->getMessage());
    fclose($fp);
}
