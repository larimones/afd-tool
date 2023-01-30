<?php

require_once 'helpers/StringHelper.php';
require_once 'entities/Production.php';
require_once 'entities/Rule.php';
require_once 'functions.php';
require_once __DIR__ . '/vendor/autoload.php';

use Entities\Grammar;
use Garden\Cli\Cli;

try {

    yellow("Welcome to Matheus and Larissa`s AFD Tool\n");

    error_reporting(E_ERROR | E_PARSE);

    $cli = new Cli();

    $cli->description('Implementa a conversão de GRs em AFDs')
        ->opt('grammar:g', 'Caminho para o arquivo com a GR.');

    $args = $cli->parse($argv, true);

    $ds = DIRECTORY_SEPARATOR;
    $grammar_path = $args->getOpt('gr', __DIR__ . $ds . 'grammar');

    white("Reading instructions from file");

    $metadata = get_and_validate_grammar_file($grammar_path);

    $tokens = get_tokens_from_grammar_file($metadata);
    if (count($tokens) > 0) {
        $grammar_from_tokens = new Grammar();
        read_tokens_from_file($grammar_from_tokens, $tokens);
        green("Successfully processed tokens from file");
    }

    $grammar_from_file_as_array = get_grammar_from_grammar_file($metadata);
    if (count($grammar_from_file_as_array) > 0) {
        $grammar_from_file = new Grammar();
        read_grammar_from_file($grammar_from_file, $grammar_from_file_as_array);
        green("Successfully processed BNF grammar from file");
    }

    $grammar = unify_grammars($grammar_from_tokens, $grammar_from_file);

    //print_grammar_in_cmd($grammar);

    $afnd = convert_grammar_into_matrix($grammar);
    $afnd_file_name = "output_files/non_deterministic_finite_automaton";
    print_matrix_into_file($afnd, $afnd_file_name, "Autômato Finito Não Determinístico");
    green("Successfully printed AFND into file {$afnd_file_name}.html");

    yellow("Transforming grammar into AFD");
    transform_grammar_in_deterministic_finite_automaton($grammar);
    green("Successfully transformed grammar into AFD");

    //print_grammar_in_cmd($grammar);

    $afd = convert_grammar_into_matrix($grammar);
    $afd_file_name = "output_files/deterministic_finite_automaton";
    print_matrix_into_file($afd, $afd_file_name, "Autômato Finito Determinístico");
    green("Successfully printed AFD into file {$afd_file_name}.html");

} catch (Exception $e) {
    magenta("Oops, we found an error while processing your request, please contact our development team to solve it.");
    $fp = fopen("error_log", 'a+');
    fwrite($fp, $e->getMessage());
    fclose($fp);
}
