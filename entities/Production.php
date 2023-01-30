<?php

namespace Entities;

class Production
{
    /**
     * @var string|null
     */
    private ?string $terminal;
    /**
     * @var string|null
     */
    private ?string $non_terminal;

    /**
     *
     */
    public function __construct()
    {
        $this->terminal = null;
        $this->non_terminal = null;
    }

    /**
     * @return string|null
     */
    public function get_non_terminal(): ?string
    {
        return $this->non_terminal;
    }

    /**
     * @return string|null
     */
    public function get_terminal(): ?string
    {
        return $this->terminal;
    }

    /**
     * @param string $non_terminal
     * @return void
     */
    public function set_non_terminal(string $non_terminal): void
    {
        $this->non_terminal = $non_terminal;
    }

    /**
     * @param string $terminal
     * @return void
     */
    public function set_terminal(string $terminal): void
    {
        $this->terminal = $terminal;
    }
}
