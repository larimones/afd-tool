<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/configuration/dependency-injection.php';

use Garden\Cli\Cli;
use Helpers\CommandLineHelper;

try {

    $grammar_factory = $containerBuilder->get('GrammarFactory');
    $lexical_analyser_service = $containerBuilder->get('LexicalAnalyserService');
    $parser_table_mapper = $containerBuilder->get('ParserTableMapper');
    $syntactical_analyser_service = $containerBuilder->get("SyntacticalAnalyserService");

    CommandLineHelper::print_yellow_message("Welcome to Laritheus\n");

    error_reporting(E_ERROR | E_PARSE);

    $cli = new Cli();

    $cli->description('Implementa o analisador léxico da linguagem Laritheus')
        ->opt('grammar:grammar', 'Caminho para o arquivo com a GR.')
        ->opt('syntactical_grammar:syntactical_grammar', 'Caminho para o arquivo com a GLC.')
        ->opt('parser:parser', "Caminho para o arquivo com a tabela Parser")
        ->opt('code:code', 'Caminho para o arquivo com o código a ser analisado.');

    $args = $cli->parse($argv, true);

    $ds = DIRECTORY_SEPARATOR;
    $grammar_path = $args->getOpt('grammar', "input_files/lexical_grammars/grammar");
    $code_path = $args->getOpt('code', "input_files/codes/code");
    $parser_path = $args->getOpt('parser', "input_files/syntactical_grammars/grammar.html");
    $syntactical_grammar_path = $args->getOpt('syntactical_grammar', "input_files/syntactical_grammars/grammar");

    $grammar = $grammar_factory->createGrammar($grammar_path);

    $symbol_table =[];
    $tape = [];

    $lexical_analyser_service->execute($grammar, $code_path, $symbol_table, $tape);

    $production_number_dictionary = [];

    $parser_table = $parser_table_mapper->execute($syntactical_grammar_path, $parser_path, $production_number_dictionary);

    $syntactical_analyser_service->execute($symbol_table, $tape, $parser_table, $production_number_dictionary);


} catch (Exception $e) {
    CommandLineHelper::print_magenta_message("Oops, we found an error while processing your request, please contact our development team to solve it.");
    $fp = fopen("error_log", 'a+');
    fwrite($fp, $e->getMessage());
    fclose($fp);
}

