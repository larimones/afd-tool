<?php

namespace Entities;

use Configuration\Configuration;

class Grammar
{
    /**
     * @var array
     */
    private array $rules;

    public function __construct()
    {
        $this->rules = [];

        $initial_rule = new Rule(Configuration::get_init_rule_name());

        $this->add_rule($initial_rule);
    }

    /**
     * @param Rule $rule
     * @return void
     */
    public function add_rule(Rule $rule) : void
    {
        $this->rules[] = $rule;
    }

    /**
     * @return array
     */
    public function get_rules(): array
    {
        return $this->rules;
    }

    /**
     * @param string $name
     * @return Rule|null
     */
    public function get_rule_by_name(string $name): ?Rule
    {
        foreach ($this->rules as $rule) {
            if ($rule->get_name() == $name) {
                return $rule;
            }
        }
        return NULL;
    }

    /**
     * @return array
     */
    public function get_all_terminals(): array
    {
        $all_terminals = [];
        foreach ($this->rules as $rule) {
            foreach ($rule->get_productions() as $production) {
                $all_terminals[] = $production->get_terminal();
            }
        }

        $all_terminals = array_unique($all_terminals);

        sort($all_terminals);

        return $all_terminals;
    }

    /**
     * @return array
     */
    public function get_all_non_terminals(): array
    {
        foreach ($this->rules as $rule) {
            $all_non_terminals[] = $rule->get_name();
        }

        sort($all_non_terminals);

        return $all_non_terminals;
    }

    /**
     * @return array
     */
    public function get_unreachable_rules(): array
    {
        $initial_rule = $this->get_rule_by_name(Configuration::get_init_rule_name());
        $reachable_states = [];

        foreach ($initial_rule->get_productions() as $production) {
            $reachable_states[] = $production->get_non_terminal();
        }

        $reachable_states = array_filter(array_unique($reachable_states));
        sort($reachable_states);

        if (count($reachable_states) > 0) {
            $i = 0;
            while (true) {
                if (!isset($reachable_states[$i])) {
                    break;
                }

                $rule_name = $reachable_states[$i];
                $rule = $this->get_rule_by_name($rule_name);

                $array = [];
                foreach ($rule->get_productions() as $production) {
                    $array[] = $production->get_non_terminal();
                }

                foreach (array_unique($array) as $rule) {
                    if (!in_array($rule, $reachable_states))
                        $reachable_states[] = $rule;
                }
                $i++;
            }
        }

        $all_non_terminals = $this->get_all_non_terminals();

        if (!in_array(Configuration::get_init_rule_name(), $reachable_states))
            $reachable_states[] = Configuration::get_init_rule_name();

        return array_diff($all_non_terminals, $reachable_states);
    }

    /**
     * @return array
     */
    public function get_dead_rules(): array
    {
        $dead_states = [];

        foreach ($this->get_rules() as $rule) {
            if ($rule->is_dead()) {
                $dead_states[] = $rule->get_name();
            }
        }

        return $dead_states;
    }

    /**
     * @return Rule|null
     */
    public  function get_init_rule(): ?Rule {
        return $this->get_rule_by_name(Configuration::get_init_rule_name());
    }
}