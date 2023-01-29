<?php

class Production
{
    private ?string $terminal;
    private ?string $non_terminal;

    public function __construct()
    {
        $this->terminal = null;
        $this->non_terminal = null;
    }

    public function get_non_terminal(): ?string
    {
        return $this->non_terminal;
    }

    public function get_terminal(): ?string
    {
        return $this->terminal;
    }

    public function set_non_terminal(string $non_terminal): void
    {
        $this->non_terminal = $non_terminal;
    }

    public function set_terminal(string $terminal): void
    {
        $this->terminal = $terminal;
    }
}
