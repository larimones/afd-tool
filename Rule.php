<?php

class Rule
{
    private string $name;

    private array $productions;

    private bool $is_final;

    public function __construct($name, $is_final = false)
    {
        $this->name = $name;
        $this->is_final = $is_final;
        $this->productions = [];
    }


    /**
     * @return bool
     */
    public function get_is_final(): bool
    {
        return $this->is_final;
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
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * @return void
     */
    public function setName(string $name): void
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
    public function getProductions(): array
    {
        return $this->productions;
    }

    public function getNonTerminalsByTerminals()
    {
        //todo: fazer código certo

        $terminals = [];

        foreach ($this->productions as $production) {
            array_push($terminals, $production->getTerminal());
        }

        $terminals = array_unique($terminals);

        $array = [];

        foreach ($terminals as $terminal) {
            $array2 = [];

            foreach ($this->productions as $production) {

                if ($production->getTerminal() == $terminal) {
                    $array2[] = $production->getNonTerminal();
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
