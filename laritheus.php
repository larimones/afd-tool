<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/configuration/dependency-injection.php';

use Garden\Cli\Cli;
use Helpers\CommandLineHelper;

try {

    $grammar_factory = $containerBuilder->get('GrammarFactory');
    $lexical_analyser_service = $containerBuilder->get('LexicalAnalyserService');
    $parser_table_mapper = $containerBuilder->get('ParserTableMapper');

    CommandLineHelper::print_yellow_message("Welcome to Laritheus\n");

    error_reporting(E_ERROR | E_PARSE);

    $cli = new Cli();

    $cli->description('Implementa o analisador léxico da linguagem Laritheus')
        ->opt('grammar:grammar', 'Caminho para o arquivo com a GR.')
        ->opt('parser:parser', "Caminho para o arquivo com a tabela Parser")
        ->opt('code:code', 'Caminho para o arquivo com o código a ser analisado.');

    $args = $cli->parse($argv, true);

    $ds = DIRECTORY_SEPARATOR;
    $grammar_path = $args->getOpt('grammar', __DIR__ . $ds . 'grammar');
    $code_path = $args->getOpt('code', __DIR__ . $ds . 'code');
    $parser_path = $args->getOpt('parser', __DIR__ . $ds . 'parser');

    $grammar = $grammar_factory->createGrammar($grammar_path);

    $symbol_table =[];
    $tape = [];

    $lexical_analyser_service->execute($grammar, $code_path, $symbol_table, $tape);

    //var_dump($tape);
    //var_dump($symbol_table);

    $parser_table = $parser_table_mapper->convert_file_to_matrix($parser_path);


} catch (Exception $e) {
    CommandLineHelper::print_magenta_message("Oops, we found an error while processing your request, please contact our development team to solve it.");
    $fp = fopen("error_log", 'a+');
    fwrite($fp, $e->getMessage());
    fclose($fp);
}

