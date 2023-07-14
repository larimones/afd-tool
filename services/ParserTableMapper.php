<?php

namespace Services;

use DOMWrap\Document;
use Entities\ParserTable;
use Entities\State;
use Entities\Transition;
use Enums\TransitionAction;
use Enums\TransitionType;
use Helpers\StringHelper;

class ParserTableMapper
{
    private InputFileService $input_file_service;

    public function __construct(InputFileService $input_file_service)
    {
        $this->input_file_service = $input_file_service;
    }

    public function execute($grammar_path, $parser_path, &$production_number_dictionary) : ParserTable {

        //set dictionary
        $productions = $this->input_file_service->get_and_validate_file_content($grammar_path);
        $production_number_dictionary = [];

        foreach ($productions as $production){
            $production_body = array_filter(explode(" ", StringHelper::regex("/->(.*)/i", $production)));
            $production_number_dictionary[] = [
                "production_name" => StringHelper::regex("/(.*?)->/i", $production),
                "items_to_reduce" => $production_body
            ];
        }

        //map parse table from html
        $file = file_get_contents($parser_path);

        $doc = new Document();
        $doc->html($file);

        $table = $doc->getElementById("lrTableView")->firstChild;

        $table_head = $table->firstChild;

        $tokens = [];

        foreach ($table_head->lastChild->children() as $token){
            $tokens[] = $token->getText();
        }

        $table_body = $table_head->nextSibling;

        $parser_table = new ParserTable();

        foreach ($table_body->children() as $line){

            $tds = $line->children();

            $state = new State($tds[0]->getText());

            for ($i = 1; $i < count($tds); $i++){
                $text = $tds[$i]->getText();

                if ($text == " "){
                    continue;
                }

                // é um action
                if (StringHelper::contains($text, ["s"])){
                    $next_state = StringHelper::regex("/s(\d+)/i", $text);
                    $transition = new Transition($tokens[$i -1], TransitionType::Action, $next_state, TransitionAction::Shift);
                }
                else if (StringHelper::contains($text, ["r"])){
                    $next_state = StringHelper::regex("/r(\d+)/i", $text);
                    $transition = new Transition($tokens[$i-1],TransitionType::Action, $next_state, TransitionAction::Reduce);
                }
                else if (StringHelper::contains($text, ["acc"])){
                    $transition = new Transition($tokens[$i-1],TransitionType::Action, null, TransitionAction::Accept);
                }
                // é um goto
                else {
                    $transition = new Transition($tokens[$i-1],TransitionType::Goto, $text);
                }

                $state->add_transition($transition);
            }
            $parser_table->add_state($state);
        }

        return $parser_table;
    }
}