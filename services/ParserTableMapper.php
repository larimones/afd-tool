<?php

namespace Services;

use DOMWrap\Document;
use Entities\ParserTable;
use Entities\State;
use Entities\Transition;
use Helpers\StringHelper;

class ParserTableMapper
{
    public function convert_file_to_matrix($parser_path) : ParserTable {

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
                    $transition = new Transition($tokens[$i -1], 0, $next_state, 0);
                }
                else if (StringHelper::contains($text, ["r"])){
                    $next_state = StringHelper::regex("/r(\d+)/i", $text);
                    $transition = new Transition($tokens[$i-1],0, $next_state, 1);
                }
                else if (StringHelper::contains($text, ["acc"])){
                    $transition = new Transition($tokens[$i-1],0, null, 3);
                }
                else {
                    $transition = new Transition($tokens[$i-1],1, $text);
                }

                $state->add_transition($transition);
            }
            $parser_table->add_state($state);
        }

        return $parser_table;
    }
}