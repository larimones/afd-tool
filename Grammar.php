<?php

class Grammar
{
    private array $rules;

    public function __construct()
    {
        $this->rules =[];
    }

    public function add_rule(Rule $rule){
        $this->rules[] = $rule;
    }

    public function get_rules() : array {
        return $this->rules;
    }

}