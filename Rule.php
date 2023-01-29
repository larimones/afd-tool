<?php

class Rule
{
    private string $name;
    private array $productions;
    private bool $is_final;
    private bool $is_initial;
    private ?bool $is_reachable;

    public function __construct($name, $is_final = false)
    {
        $this->name = $name;
        $this->is_final = $is_final;
        $this->productions = [];
        $this->is_initial = ($name == "S");
        $this->is_reachable = NULL;
    }

    public function get_is_final(): bool
    {
        return $this->is_final;
    }

    public function get_is_initial(): bool
    {
        return $this->is_initial;
    }

    public function set_is_final(bool $is_final): void
    {
        $this->is_final = $is_final;
    }

    public function get_name(): string
    {
        return $this->name;
    }

    public function set_name(string $name): void
    {
        $this->name = $name;
    }

    public function add_production(Production $production)
    {
        $this->productions[] = $production;
    }

    public function get_productions(): array
    {
        return $this->productions;
    }

    public function get_non_terminals_by_terminals($terminals)
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

            array_push($array, [
                "{$terminal}" => $non_terminals
            ]);
        }

        arsort($array);

        return $array;
    }

    public function remove_production_by_terminal_and_non_terminal($terminal, $non_terminal)
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

    public function get_production_by_terminal_and_non_terminal($terminal, $non_terminal)
    {
        foreach ($this->productions as $production){
            if ($production->get_terminal() == $terminal && $production->get_non_terminal() == $non_terminal) {

                return $production;
            }
        }

        return null;
    }

    public function get_productions_by_terminal($terminal)
    {
        $array = [];

        foreach ($this->productions as $production){
            if ($production->get_terminal() == $terminal) {
                $array[] = $production;
            }
        }

        return $array;
    }

    public function is_dead(): bool
    {
        $reachable_states = [];
        foreach ($this->productions as $production) {
            $reachable_states[] = $production->get_non_terminal();
        }

        $reachable_states = array_unique($reachable_states);
        // todo: Validar com o professor se M continua sendo morto depois de adicionar o estado de erro

        return (count($reachable_states) == 1 && in_array($this->name, $reachable_states)) ? true : false;
    }

    public function get_is_reachable() : ?bool {
        return $this->is_reachable;
    }

    public function set_is_reachable(?bool $is_reachable) : void {
        $this->is_reachable = $is_reachable;
    }

    public function remove_all_productions_by_terminal($terminal) : void {
        foreach ($this->productions as $production){
            if ($production->get_terminal() == $terminal){
                $index = array_search($production, $this->productions);

                unset($this->productions[$index]);
                sort($this->productions);
            }
        }
    }
}
