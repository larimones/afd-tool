<?php

require_once 'Rule.php';

class Grammar
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [];

        $initial_rule = new Rule("S");

        $this->add_rule($initial_rule);
    }

    public function add_rule(Rule $rule)
    {
        $this->rules[] = $rule;
    }

    public function get_rules(): array
    {
        return $this->rules;
    }

    public function get_rule_by_name(string $name): ?Rule
    {
        foreach ($this->rules as $rule) {
            if ($rule->getName() == $name) {
                return $rule;
            }
        }
        return NULL;
    }
}
