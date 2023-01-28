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
     * @return void
     */
    public function set_name(string $name): void
    {
        $this->name = $name;
    }

    public function add_production(Production $production)
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

    public function get_non_terminals_by_terminals($terminals)
    {
        //todo: fazer código certo

        $array = [];

        foreach ($terminals as $terminal) {
            $array2 = [];
            $found = false;

            foreach ($this->productions as $production) {
                if ($production->get_terminal() == $terminal) {
                    $found = true;
                    $array2[] = $production->get_non_terminal();
                }
            }

            if (!$found)
                $array2[] = "-";

            $array2 = array_unique($array2);

            sort($array2);

            array_push($array, [
                "{$terminal}" => $array2
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

    /**
     * @return bool
     */
    public function is_dead(): bool
    {
        $reachable_states = [];
        foreach ($this->productions as $production) {
            $reachable_states[] = $production->get_non_terminal();
        }

        $reachable_states = array_unique($reachable_states);

        return (count($reachable_states) == 1 && in_array($this->name, $reachable_states)) ? true : false;
    }

    public function get_is_reachable() : ?bool {
        return $this->is_reachable;
    }

    public function set_is_reachable($is_reachable) : void {
        $this->is_reachable = $is_reachable;
    }
}
