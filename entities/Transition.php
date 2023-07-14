<?php

namespace Entities;

use Enums\TransitionAction;
use Enums\TransitionType;

class Transition
{
    private TransitionType $type;
    private ?int $next_state;

    //null é um goto
    private ?TransitionAction $action;

    private string $token;

    public function __construct(string $token, TransitionType $type, int $next_state = null, TransitionAction $action = null )
    {
        $this->token = $token;
        $this->type = $type;
        $this->next_state = $next_state;
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function get_token(): string
    {
        return $this->token;
    }

    /**
     * @return TransitionType
     */
    public function get_type(): TransitionType
    {
        return $this->type;
    }

    /**
     * @return int|null
     */
    public function get_next_state(): ?int
    {
        return $this->next_state;
    }

    /**
     * @return TransitionAction|null
     */
    public function get_action(): ?TransitionAction
    {
        return $this->action;
    }
}