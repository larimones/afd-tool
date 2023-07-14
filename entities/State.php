<?php

namespace Entities;

class State
{
    private int $id;
    private array $transitions;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function add_transition(Transition $transition) : void {
        $this->transitions[] = $transition;
    }

    public function get_id(): int {
        return $this->id;
    }

    public function get_transition_by_token(string $token) : ?Transition {
        foreach ($this->transitions as $transition){
            if ($transition->get_token() == $token){
                return $transition;
            }
        }
        return null;
    }
}