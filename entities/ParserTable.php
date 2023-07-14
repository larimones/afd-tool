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

    public function get_state_by_id(int $id) : ?State {
        foreach ($this->states as $state){
            if ($state->get_id() == $id){
                return $state;
            }
        }
        return null;
    }
}