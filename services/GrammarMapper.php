<?php

namespace Services;

use Entities\Grammar;
use Entities\Production;
use Entities\Rule;
use Helpers\CommandLineHelper;
use Helpers\StringHelper;

class GrammarMapper
{
    /**
     * @param Grammar $grammar
     * @param array $tokens
     * @return void
     */
    public static function from_tokens(Grammar &$grammar, array $tokens): void
    {
        $count_of_rules = count($grammar->get_rules());

        foreach ($tokens as $token) {
            $token = trim($token);
            $token_as_array = array_filter(str_split($token));

            for ($i = 0; $i < count($token_as_array); $i++) {
                if ($i == 0) {
                    $rule = $grammar->get_rule_by_name("S");

                    $production = new Production();
                    $production->set_terminal($token_as_array[$i]);
                    $production->set_non_terminal(StringHelper::convert_number_to_alphabet($count_of_rules + 1));

                    $rule->add_production($production);
                } else {
                    $count_of_rules++;
                    $rule = new Rule(StringHelper::convert_number_to_alphabet($count_of_rules));

                    $production = new Production();
                    $production->set_terminal($token_as_array[$i]);
                    $production->set_non_terminal(StringHelper::convert_number_to_alphabet($count_of_rules + 1));

                    $rule->add_production($production);
                    $grammar->add_rule($rule);
                }
            }

            $count_of_rules++;
            $rule = new Rule(StringHelper::convert_number_to_alphabet($count_of_rules), true);
            $grammar->add_rule($rule);
        }
    }

    /**
     * @param Grammar $grammar
     * @param array $raw_rules
     * @return void
     */
    public static function from_bfn_regular_grammar(Grammar &$grammar, array $raw_rules): void
    {
        $count_of_raw_rules = count($raw_rules);

        // todo: validar com professor se é necessário a criação do estado "X"
        $should_create_finish_state = false;

        foreach ($raw_rules as $raw_rule) {
            $raw_rule = explode("::=", $raw_rule);

            $name = StringHelper::regex("/<(.)>/i", $raw_rule[0]);

            $rule = ($name == "S") ? $grammar->get_rule_by_name("S") : new Rule($name);

            // todo: validar com o professor se essa sintaxe se aplica ao BNF tbm ou se só consideramos o ε
            $is_final = StringHelper::contains($raw_rule[0], "*");
            $rule->set_is_final($is_final);

            $raw_productions = explode("|", $raw_rule[1]);

            foreach ($raw_productions as $raw) {
                if (StringHelper::contains($raw, "ε")) {
                    // todo: validar com o professor se realmente só marcamos o estado como final, ou se criamos o estado "X" tbm
                    $rule->set_is_final(true);
                } else if (!StringHelper::contains($raw, ["<", ">"])) {
                    $production = new Production();
                    $terminal = trim($raw);
                    $production->set_terminal($terminal);
                    $production->set_non_terminal(StringHelper::convert_number_to_alphabet($count_of_raw_rules + 1));
                    $should_create_finish_state = true;
                } else {
                    $terminal = StringHelper::regex("/(.)</i", $raw);
                    $non_terminal = StringHelper::regex("/<(.*?)>/i", $raw);

                    $production = new Production();
                    $production->set_non_terminal($non_terminal);
                    $production->set_terminal($terminal);
                }

                $rule->add_production($production);
            }

            if ($rule->get_name() != "S")
                $grammar->add_rule($rule);
        }

        // todo: se for pra criar o estado "X" mesmo, devemos criar apenas um ou vários como fazemos no processamento dos tokens?
        if ($should_create_finish_state) {
            $rule = new Rule(StringHelper::convert_number_to_alphabet($count_of_raw_rules + 1), true);
            $grammar->add_rule($rule);
        }
    }

    /**
     * @param Grammar $grammar1
     * @param Grammar $grammar2
     * @return mixed
     */
    public static function unify_grammars(Grammar $grammar1, Grammar $grammar2): Grammar
    {
        if (!isset($grammar1) and isset($grammar2)) {
            return $grammar2;
        } else if (isset($grammar1) and !isset($grammar2)) {
            return $grammar1;
        } else {

            $count_of_rules = count($grammar1->get_rules());
            $rules_names = [];

            foreach ($grammar2->get_rules() as $rule) {
                if ($rule->get_name() == "S") {
                    continue;
                }

                $count_of_rules++;
                $rules_names[] = [
                    "{$rule->get_name()}" => $count_of_rules
                ];
            }

            foreach ($rules_names as $rule_name) {
                foreach ($grammar2->get_rules() as $rule) {
                    if ($rule->get_name() == key($rule_name)) {
                        $rule->set_name(StringHelper::convert_number_to_alphabet($rule_name[key($rule_name)]));
                    }
                    foreach ($rule->get_productions() as $production) {
                        if ($production->get_non_terminal() == key($rule_name)) {
                            $production->set_non_terminal(StringHelper::convert_number_to_alphabet($rule_name[key($rule_name)]));
                        }
                    }
                }
            }

            foreach ($grammar2->get_rules() as $rule) {
                if ($rule->get_name() == "S") {
                    $ruleSGrammar1 = $grammar1->get_rule_by_name("S");

                    foreach ($rule->get_productions() as $production) {
                        $ruleSGrammar1->add_production($production);
                    }
                } else {
                    $grammar1->add_rule($rule);
                }
            }

            FiniteAutomatonService::set_unreachable_rules($grammar1);

            CommandLineHelper::print_green_message("Successfully merged grammars from file");

            return $grammar1;
        }
    }

    /**
     * @param Grammar $grammar
     * @return array
     */
    public static function from_grammar_to_matrix(Grammar $grammar): array
    {
        $terminals = $grammar->get_all_terminals();
        $rules = $grammar->get_rules();

        $matrix[0][0] = '';
        $matrix[0][1] = 'δ';

        $i = 2;
        foreach ($terminals as $terminal) {
            $matrix[0][$i] = $terminal;
            $i++;
        }

        $i = 1;
        foreach ($rules as $rule) {
            $characteristics = [];

            if (json_encode($rule->get_is_reachable()) == "false") {
                $characteristics[] = "∞";
            }

            if ($rule->is_dead()) {
                $characteristics[] = "✝";
            }

            if ($rule->get_is_final()) {
                $characteristics[] = "*";
            }

            if ($rule->get_is_initial()) {
                $characteristics[] = "→";
            }

            // todo: Validar com o professor se podemos exibir os estados inalcançáveis assim mesmo, com a coluna extra
            $matrix[$i][0] = join(" ", $characteristics);
            $matrix[$i][1] = $rule->get_name();

            $transitions = $rule->get_non_terminals_by_terminals($terminals);

            $j = 2;
            foreach ($transitions as $transition) {

                foreach ($transition as $next_rules) {
                    $rules = implode('', $next_rules);
                }
                $matrix[$i][$j] = $rules;
                $j++;
            }
            $i++;
        }

        return $matrix;
    }
}