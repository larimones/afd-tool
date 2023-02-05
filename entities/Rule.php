<?php

namespace Entities;

class Rule
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var array
     */
    private array $productions;
    /**
     * @var bool|mixed
     */
    private bool $is_final;

    /**
     * @var bool
     */
    private bool $is_initial;

    /**
     * @var bool|null
     */
    private ?bool $is_reachable;

    /**
     * @param string $name
     * @param bool $is_final
     */
    public function __construct(string $name, bool $is_final = false)
    {
        $this->name = $name;
        $this->is_final = $is_final;
        $this->productions = [];
        $this->is_initial = ($name == "S");
        $this->is_reachable = NULL;
    }

    /**
     * @return bool
     */
    public function get_is_final(): bool
    {
        return $this->is_final;
    }

    /**
     * @return bool
     */
    public function get_is_initial(): bool
    {
        return $this->is_initial;
    }

    /**
     * @param bool $is_final
     * @return void
     */
    public function set_is_final(bool $is_final): void
    {
        $this->is_final = $is_final;
    }

    /**
     * @return string
     */
    public function get_name(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function set_name(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param Production $production
     * @return void
     */
    public function add_production(Production $production): void
    {
        $this->productions[] = $production;
    }

    /**
     * @return array
     */
    public function get_productions(): array
    {
        return $this->productions;
    }

    /**
     * @param array $terminals
     * @return array
     */
    public function get_non_terminals_by_terminals(array $terminals): array
    {
        $array = [];

        foreach ($terminals as $terminal) {
            $non_terminals = [];
            $found = false;

            foreach ($this->productions as $production) {
                if ($production->get_terminal() == $terminal) {
                    $found = true;
                    $non_terminals[] = $production->get_non_terminal();
                }
            }

            if (!$found)
                $non_terminals[] = "-";

            $non_terminals = array_unique($non_terminals);

            sort($non_terminals);

            $array[] = [
                "{$terminal}" => $non_terminals
            ];
        }

        arsort($array);

        return $array;
    }

    /**
     * @param string|null $terminal
     * @param string|null $non_terminal
     * @return void
     */
    public function remove_production_by_terminal_and_non_terminal(?string $terminal, ?string $non_terminal): void
    {
        foreach ($this->productions as $production){
            if ($production->get_terminal() == $terminal && $production->get_non_terminal() == $non_terminal) {
                $index = array_search($production, $this->productions);

                unset($this->productions[$index]);
                sort($this->productions);
                return;
            }
        }
    }

    /**
     * @param string|null $terminal
     * @param string|null $non_terminal
     * @return mixed|null
     */
    public function get_production_by_terminal_and_non_terminal(?string $terminal, ?string $non_terminal): mixed
    {
        foreach ($this->productions as $production){
            if ($production->get_terminal() == $terminal && $production->get_non_terminal() == $non_terminal) {

                return $production;
            }
        }

        return null;
    }

    /**
     * @param string|null $terminal
     * @return array
     */
    public function get_productions_by_terminal(?string $terminal): array
    {
        $array = [];

        foreach ($this->productions as $production){
            if ($production->get_terminal() == $terminal) {
                $array[] = $production;
            }
        }

        return $array;
    }

    /**
     * @return bool
     */
    public function is_dead(): bool
    {
        if ($this->is_final){
            return false;
        }

        $reachable_states = [];
        foreach ($this->productions as $production) {
            $reachable_states[] = $production->get_non_terminal();
        }

        $reachable_states = array_unique($reachable_states);

        return count($reachable_states) == 1 && in_array($this->name, $reachable_states);
    }

    /**
     * @return bool|null
     */
    public function get_is_reachable() : ?bool {
        return $this->is_reachable;
    }

    /**
     * @param bool|null $is_reachable
     * @return void
     */
    public function set_is_reachable(?bool $is_reachable) : void {
        $this->is_reachable = $is_reachable;
    }

    /**
     * @param string|null $terminal
     * @return void
     */
    public function remove_all_productions_by_terminal(?string $terminal) : void {
        foreach ($this->productions as $production){
            if ($production->get_terminal() == $terminal){
                $index = array_search($production, $this->productions);

                unset($this->productions[$index]);
                sort($this->productions);
            }
        }
    }
}
