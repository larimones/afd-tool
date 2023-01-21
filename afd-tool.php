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

    //print_grammar_in_cmd($grammar);

    $afnd = convert_grammar_into_matrix($grammar);

    print_matrix_into_file($afnd, "non_deterministic_finite_automaton");

    generate_deterministic_finite_automaton($grammar);

    //print_grammar_in_cmd($grammar);

    $afd = convert_grammar_into_matrix($grammar);

    print_matrix_into_file($afd, "deterministic_finite_automaton");

    var_dump($grammar->get_unreachable_rules());
    var_dump($grammar->get_dead_rules());
} catch (Exception $e) {
}
