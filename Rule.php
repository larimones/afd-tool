<?php

class Rule
{
    private string $name;

    private array $productions;

    public function __construct($name)
    {
        $this->name = $name;
        $this->productions = [];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function add_production(Production $production){
        $this->productions[] = $production;
    }

    /**
     * @return array
     */
    public function getProductions(): array
    {
        return $this->productions;
    }

    public function getNonTerminalsByTerminals(){
        //todo: fazer código certo

        $terminals =[];

        foreach ($this->productions as $production){
            array_push($terminals, $production->getTerminal());
        }

        $terminals = array_unique($terminals);

        return $terminals;
        $terminals_with_non_terminals = [];

        foreach ($terminals as $terminal){
            foreach ($this->productions as $production){
                if ($production->getTerminal() == $terminal){
                    $terminals_with_non_terminals[] = $production->getNonTerminal();
                }
            }
        }

        //return array_unique($terminals_with_non_terminals);
    }

}