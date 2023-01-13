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

    //todo: extract this to a method

    $grammar = new Grammar();

    foreach ($metadata as $value){
        $value = explode("::=", $value);
        $name = StringHelper::regex("/<(.)>/i", $value[0]);

        $rule = new Rule($name);

        $raw_productions = explode("|", $value[1]);

        foreach ($raw_productions as $raw){
            if (StringHelper::contains($raw, "ε")){
                $production = new Production();
                $production->setTerminal("ε");
            }
            else if (!StringHelper::contains($raw, ["<", ">"])){
                $production = new Production();
                $terminal = trim($raw);
                $production->setTerminal($terminal);
            }
            else {
                $terminal_before_non_terminal = StringHelper::regex("/(.)</i", $raw);
                $terminal_after_non_terminal = StringHelper::regex("/>(.)/i", $raw);
                $non_terminal = StringHelper::regex("/<(.*?)>/i", $raw);
                $production = new Production();
                $production->setNonTerminal($non_terminal);

                if ($terminal_before_non_terminal != "" && $terminal_after_non_terminal == ""){
                    $production->setTerminal($terminal_before_non_terminal);
                }

                if ($terminal_after_non_terminal != "" && $terminal_before_non_terminal == "") {
                    $production->setTerminal($terminal_after_non_terminal);
                }
            }
            $rule->add_production($production);
        }
        $grammar->add_rule($rule);
    }

    //todo: these are all tests

    foreach ($grammar->get_rules() as $rule){
        $teste = "";

        foreach ($rule->getProductions() as $prod){
            var_dump($rule->getNonTerminalsByTerminals());
            $teste = "{$teste}|{$prod->get_production()}";
        }

        //print_r("{$rule->getName()} {$teste}\n");
    }
}
catch (Exception $e){

}