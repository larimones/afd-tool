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
    //CommandLineHelper::print_white_message("Reading instructions from file");

    $metadata = $input_file_service->get_and_validate_file_content($grammar_path);

    $tokens = $input_file_service->get_tokens_from_grammar_file($metadata);
    if (count($tokens) > 0) {
        $grammar_from_tokens = new Grammar();
        $grammar_mapper->from_tokens($grammar_from_tokens, $tokens);
    }

    $grammar_from_file_as_array = $input_file_service->get_grammar_from_grammar_file($metadata);
    if (count($grammar_from_file_as_array) > 0) {
        $grammar_from_file = new Grammar();
        $grammar_mapper->from_bnf_regular_grammar($grammar_from_file, $grammar_from_file_as_array);
    }

    $grammar = $grammar_mapper->unify_grammars($grammar_from_tokens, $grammar_from_file);
    $finite_automaton_service->transform_grammar_in_deterministic_finite_automaton($grammar);
    $afd = $print_service->from_grammar_to_matrix($grammar);
    $afd_file_name = "output_files/deterministic_finite_automaton";
    $print_service->matrix_to_file($afd, $afd_file_name, "Autômato Finito Determinístico");

    $code_lines = $input_file_service->get_and_validate_file_content($code_path);

    $lines_size = [];

    for ($i =0; $i < count($code_lines); $i++){
        $lines_size[] = [
            "line" => $i + 1,
            //conta mais por que juntamos as linhas com separador
            "size" => count(str_split($code_lines[$i])) + 1
        ];
    }

    $code = join(" ", $code_lines) . " ";

    $fp1 = fopen('fita.csv', 'w');
    $fp2 = fopen("tabela_tokens.csv", "w");
    fputcsv($fp2, ["id", "rotulo", "linha"]);
    $table = [];

    $e = $grammar->get_rule_by_name(Configuration::get_init_rule_name());

    $token = null;

    $number_of_tokens_read = 0;

    foreach (str_split($code) as $character) {
        if ($character == " ") {

            $acumulador = 0;

            foreach ($lines_size as $line_size){
                $acumulador += $line_size["size"];

                if ($number_of_tokens_read <= $acumulador){
                    $line = $line_size["line"];
                    break;
                }
            }

            if ($e->get_is_final()) {
                //is int
                if ($e->get_name() == "DH") {
                    fputcsv($fp1, ["integer", $token]);
                } // is decimal
                else if ($e->get_name() == "DI") {
                    fputcsv($fp1, ["decimal", $token]);
                } //is id // string or var
                else if ($e->get_name() == "DJ" or $e->get_name() == "DG") {
                    $id = null;
                    foreach ($table as $item){
                        if ($item["rotulo"] == $token){
                            $id = $item["id"];
                            break;
                        }
                    }

                    if ($id == null){
                        $id = count($table) + 1;

                        $table[] = [
                            "id" => $id,
                            "rotulo" => $token,
                            "linha" => $line
                        ];

                        fputcsv($fp2, [$id, $token, $line]);
                    }

                    fputcsv($fp1, ["id", $id]);
                } else {
                    fputcsv($fp1, [$e->get_name()]);
                }
            } else {
                fputcsv($fp1, [Configuration::get_err_rule_name(), $e->get_name(), $line]);
            }
            $e = $grammar->get_rule_by_name(Configuration::get_init_rule_name());
            $next_rule_name = null;
            $token = null;
        } else {
            $token .= $character;
            $next_rule_name = $e->get_non_terminal_by_terminal($character);
            $e = $grammar->get_rule_by_name($next_rule_name) ?? $e;
        }
        $number_of_tokens_read++;
    }

} catch (Exception $e) {
    CommandLineHelper::print_magenta_message("Oops, we found an error while processing your request, please contact our development team to solve it.");
    $fp = fopen("error_log", 'a+');
    fwrite($fp, $e->getMessage());
    fclose($fp);
}
