<?php

namespace Entities;

class Transition
{
    //0 action e 1 goto, levar para enum
    private int $type;
    private ?int $next_state;

    //null é um goto, 0 = s é empilha e 1 = r é reduz e 3 é acc, levar para enum
    private ?int $action;

    private string $token;

    public function __construct(string $token, int $type, int $next_state = null, int $action = null )
    {
        $this->token = $token;
        $this->type = $type;
        $this->next_state = $next_state;
        $this->action = $action;
    }
}