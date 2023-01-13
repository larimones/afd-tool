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

    /**
     * @return mixed
     */
    public function getNonTerminal() : ?string
    {
        return $this->non_terminal;
    }

    /**
     * @return mixed
     */
    public function getTerminal() : ?string
    {
        return $this->terminal;
    }

    /**
     * @param mixed $non_terminal
     */
    public function setNonTerminal(string $non_terminal) : void
    {
        $this->non_terminal = $non_terminal;
    }

    /**
     * @param mixed $terminal
     */
    public function setTerminal(string $terminal) : void
    {
        $this->terminal = $terminal;
    }

    public function get_production() : ?string
    {
        if ($this->non_terminal == null)
            return "{$this->terminal}";
        else
            return "{$this->terminal}<{$this->non_terminal}>";
    }
}