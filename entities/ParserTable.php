<?php

namespace Entities;

class ParserTable
{
    private array $states;

    public function __construct()
    {
        $this->states = [];
    }

    public function add_state(State $state) : void {
        $this->states[] = $state;
    }
}