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
}