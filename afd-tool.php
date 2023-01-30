<?php
require_once __DIR__ . '/vendor/autoload.php';

use Entities\Grammar;
use Garden\Cli\Cli;
use Helpers\CommandLineHelper;
use Services\FiniteAutomatonService;
use Services\GrammarMapper;
use Services\InputFileService;
use Services\PrintService;

try {

    CommandLineHelper::print_yellow_message("Welcome to Matheus and Larissa`s AFD Tool\n");

    error_reporting(E_ERROR | E_PARSE);

    $cli = new Cli();

    $cli->description('Implementa a conversão de GRs em AFDs')
        ->opt('grammar:g', 'Caminho para o arquivo com a GR.');

    $args = $cli->parse($argv, true);

    $ds = DIRECTORY_SEPARATOR;
    $grammar_path = $args->getOpt('gr', __DIR__ . $ds . 'grammar');

    CommandLineHelper::print_white_message("Reading instructions from file");

    $metadata = InputFileService::get_and_validate_grammar_file($grammar_path);

    $tokens = InputFileService::get_tokens_from_grammar_file($metadata);
    if (count($tokens) > 0) {
        $grammar_from_tokens = new Grammar();
        GrammarMapper::from_tokens($grammar_from_tokens, $tokens);
        CommandLineHelper::print_green_message("Successfully processed tokens from file");
    }

    $grammar_from_file_as_array = InputFileService::get_grammar_from_grammar_file($metadata);
    if (count($grammar_from_file_as_array) > 0) {
        $grammar_from_file = new Grammar();
        GrammarMapper::from_bfn_regular_grammar($grammar_from_file, $grammar_from_file_as_array);
        CommandLineHelper::print_green_message("Successfully processed BNF grammar from file");
    }

    $grammar = GrammarMapper::unify_grammars($grammar_from_tokens, $grammar_from_file);

    //print_grammar_in_cmd($grammar);

    $afnd = GrammarMapper::from_grammar_to_matrix($grammar);
    $afnd_file_name = "output_files/non_deterministic_finite_automaton";
    PrintService::matrix_to_file($afnd, $afnd_file_name, "Autômato Finito Não Determinístico");
    CommandLineHelper::print_green_message("Successfully printed AFND into file {$afnd_file_name}.html");

    CommandLineHelper::print_yellow_message("Transforming grammar into AFD");
    FiniteAutomatonService::transform_grammar_in_deterministic_finite_automaton($grammar);
    CommandLineHelper::print_green_message("Successfully transformed grammar into AFD");

    //print_grammar_in_cmd($grammar);

    $afd = GrammarMapper::from_grammar_to_matrix($grammar);
    $afd_file_name = "output_files/deterministic_finite_automaton";
    PrintService::matrix_to_file($afd, $afd_file_name, "Autômato Finito Determinístico");
    CommandLineHelper::print_green_message("Successfully printed AFD into file {$afd_file_name}.html");

} catch (Exception $e) {
    CommandLineHelper::print_magenta_message("Oops, we found an error while processing your request, please contact our development team to solve it.");
    $fp = fopen("error_log", 'a+');
    fwrite($fp, $e->getMessage());
    fclose($fp);
}
