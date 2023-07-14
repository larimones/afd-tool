<?php

namespace Services;

use Entities\ParserTable;
use Enums\TransitionAction;
use Helpers\CommandLineHelper;

class SyntacticalAnalyserService
{

    public function __construct()
    {
    }

    public function execute(array &$symbol_table, array &$tape, ParserTable $parser_table, $production_number_dictionary)
    {
        $production_names = [];

        foreach ($production_number_dictionary as $item) {
            if (!in_array($item["production_name"], $production_names)) {
                $production_names[] = $item["production_name"];
            }
        }

        $i = 0;

        $symbol_stack = new \Ds\Stack();
        $state_stack = new \Ds\Stack();

        $state_stack->push(0);

        $done = false;

        while (true) {
            //system('clear');
            //print_r($symbol_stack);
            //print_r($state_stack);

            $current_state = $parser_table->get_state_by_id($state_stack->peek());

            if (!$done && !$symbol_stack->isEmpty() && in_array($symbol_stack->peek(), $production_names)) {
                $done = true;
                $transition = $current_state->get_transition_by_token($symbol_stack->peek());
            } else {
                $done = false;
                $transition = $current_state->get_transition_by_token($tape[$i]["token_type"]);
            }

            if ($transition == null) {
                $token_to_show = ($tape[$i - 1]["token_type"] == "id") ? $tape[$i - 1]["token_value"] : $tape[$i - 1]["token_type"];
                CommandLineHelper::print_magenta_message("Syntactical error near '{$token_to_show}' at line {$tape[$i-1]["line"]}");
                break;
            }


            if ($transition->get_action() == TransitionAction::Shift) {
                $i++;
                $state_stack->push($transition->get_next_state());
                $symbol_stack->push($transition->get_token());
            } else if ($transition->get_action() == TransitionAction::Reduce) {
                foreach ($production_number_dictionary[$transition->get_next_state()]["items_to_reduce"] as $_) {
                    $state_stack->pop();
                    $symbol_stack->pop();
                }
                $symbol_stack->push($production_number_dictionary[$transition->get_next_state()]["production_name"]);
            } else if ($transition->get_action() == TransitionAction::Accept) {
                CommandLineHelper::print_green_message("Syntactical Analysis Completed With No Errors");
                break;
            } else {
                $state_stack->push($transition->get_next_state());
            }
        }
    }
}