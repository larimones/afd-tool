<?php

class Rule
{
    private string $name;

    private array $productions;

    private bool $is_final;

    private bool $is_initial;

    public function __construct($name, $is_final = false)
    {
        $this->name = $name;
        $this->is_final = $is_final;
        $this->productions = [];
        $this->is_initial = ($name == "S");
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

    public function get_non_terminals_by_terminals()
    {
        //todo: fazer código certo

        $terminals = [];

        foreach ($this->productions as $production) {
            array_push($terminals, $production->get_terminal());
        }

        $terminals = array_unique($terminals);

        $array = [];

        foreach ($terminals as $terminal) {
            $array2 = [];

            foreach ($this->productions as $production) {

                if ($production->get_terminal() == $terminal) {
                    $array2[] = $production->get_non_terminal();
                }
            }

            $array2 = array_unique($array2);
            array_push($array, [
                "{$terminal}" => $array2
            ]);
        }

        return $array;
    }
}
