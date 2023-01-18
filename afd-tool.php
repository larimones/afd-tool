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

    $grammar = new Grammar();

    read_grammar_from_file($grammar, get_grammar_from_grammar_file($metadata));

    read_tokens_from_file($grammar, get_tokens_from_grammar_file($metadata));

    //todo: these are all tests

    foreach ($grammar->get_rules() as $rule) {
        $teste = "";

        foreach ($rule->getProductions() as $prod) {
            var_dump($rule->getNonTerminalsByTerminals());
            $teste = "{$teste}|{$prod->get_production()}";
        }

        //print_r("{$rule->getName()} {$teste}\n");
    }
} catch (Exception $e) {
}
