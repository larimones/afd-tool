<?php

namespace Services;

use Entities\Grammar;
use Entities\Production;
use Entities\Rule;

class FiniteAutomatonService
{
    /**
     * @param $grammar
     * @return void
     */
    public static function transform_grammar_in_deterministic_finite_automaton(Grammar $grammar): void
    {
        FiniteAutomatonService::unset_unreachable_rules($grammar);
        $terminals = $grammar->get_all_terminals();

        $j = 0;
        while (true) {
            $rules = $grammar->get_rules();

            if (!array_key_exists($j, $rules)) {
                break;
            }

            $rule = $rules[$j];

            $non_terminals_by_terminals = $rule->get_non_terminals_by_terminals($terminals);
            foreach ($non_terminals_by_terminals as $non_terminals_by_terminal) {
                $terminal = key($non_terminals_by_terminal);

                $non_terminals = array_values($non_terminals_by_terminal)[0];

                if (count($non_terminals) <= 1) {
                    continue;
                } else {
                    $new_rule_name = "[" . join($non_terminals) . "]";

                    $verify_rule_existence = $grammar->get_rule_by_name($new_rule_name);

                    if ($verify_rule_existence == NULL) {

                        $new_rule = new Rule($new_rule_name);

                        foreach ($non_terminals as $non_terminal) {
                            $reference_rule = $grammar->get_rule_by_name($non_terminal);

                            if ($reference_rule->get_is_final()) {
                                $new_rule->set_is_final(true);
                            }

                            foreach ($terminals as $t) {
                                $string_of_productions = "";
                                foreach ($reference_rule->get_productions_by_terminal($t) as $production) {
                                    $string_of_productions .= $production->get_non_terminal();
                                }

                                $string_of_productions = str_replace("[", "", $string_of_productions);
                                $string_of_productions = str_replace("]", "", $string_of_productions);

                                $array_of_productions = array_unique(str_split($string_of_productions));

                                foreach ($array_of_productions as $productions_name) {
                                    if ($new_rule->get_production_by_terminal_and_non_terminal($t, $productions_name) != null) {
                                        continue;
                                    }

                                    $production = new Production();
                                    $production->set_non_terminal($productions_name);
                                    $production->set_terminal($t);

                                    $new_rule->add_production($production);
                                }
                            }
                        }
                        $grammar->add_rule($new_rule);
                    }
                }

                $new_production = new Production();
                $new_production->set_terminal($terminal);
                $new_production->set_non_terminal($new_rule_name);

                $rule->remove_all_productions_by_terminal($terminal);

                $rule->add_production($new_production);
            }
            $j++;
        }

        FiniteAutomatonService::add_error_state_to_afd($grammar, $terminals);
        FiniteAutomatonService::set_unreachable_rules($grammar);
    }

    /**
     * @param Grammar $grammar
     * @return void
     */
    public static function set_unreachable_rules(Grammar $grammar): void
    {
        $unreachable_rules = $grammar->get_unreachable_rules();

        foreach ($grammar->get_rules() as $rule) {
            if (in_array($rule->get_name(), $unreachable_rules)) {
                $rule->set_is_reachable(false);
            } else {
                $rule->set_is_reachable(true);
            }
        }
    }

    /**
     * @param Grammar $grammar
     * @return void
     */
    private static function unset_unreachable_rules(Grammar $grammar): void
    {
        foreach ($grammar->get_rules() as $rule) {
            $rule->set_is_reachable(null);
        }
    }

    /**
     * @param Grammar $grammar
     * @param array $terminals
     * @return void
     */
    public static function add_error_state_to_afd(Grammar $grammar, array $terminals): void
    {
        $error_rule_name = "-";
        $error_rule = new Rule($error_rule_name, true);

        /*
         * REMOVED BECAUSE THE ERR STATE SHOULD NOT BE VIEWD AS AN STATE FOR THE REST OF THE GRAMMAR
        foreach ($terminals as $terminal) {
            $production = new Production();
            $production->set_terminal($terminal);
            $production->set_non_terminal($error_rule_name);

            $error_rule->add_production($production);
        }

        foreach ($grammar->get_rules() as $rule) {
            foreach ($rule->get_non_terminals_by_terminals($terminals) as $non_terminals_by_terminal) {
                $terminal = key($non_terminals_by_terminal);
                $reachable_rules = array_values($non_terminals_by_terminal);

                if (count($reachable_rules) == 1 and $reachable_rules[0][0] == "-") {
                    $production = new Production();
                    $production->set_non_terminal($error_rule_name);
                    $production->set_terminal($terminal);

                    $rule->add_production($production);
                }
            }
        }
        */

        $grammar->add_rule($error_rule);
    }

}