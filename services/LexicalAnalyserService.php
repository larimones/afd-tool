<?php

namespace Services;

use Configuration\Configuration;
use Entities\Grammar;
use Helpers\CommandLineHelper;

class LexicalAnalyserService
{
    private InputFileService $input_file_service;

    public function __construct(InputFileService $input_file_service)
    {
        $this->input_file_service = $input_file_service;
    }

    public function execute(Grammar $grammar, string $code_path, array &$symbol_table, array &$tape){

        $code_lines = $this->input_file_service->get_and_validate_file_content($code_path);

        $lines_size = [];

        for ($i =0; $i < count($code_lines); $i++){
            $lines_size[] = [
                "line" => $i + 1,
                "size" => count(str_split($code_lines[$i])) + 1
            ];
        }

        $code = join(" ", $code_lines) . " ";

        $e = $grammar->get_rule_by_name(Configuration::get_init_rule_name());

        $token = null;

        $number_of_tokens_read = 0;

        foreach (str_split($code) as $character) {
            if ($character == " ") {

                $accumulator = 0;

                foreach ($lines_size as $line_size){
                    $accumulator += $line_size["size"];

                    if ($number_of_tokens_read <= $accumulator){
                        $line = $line_size["line"];
                        break;
                    }
                }

                if ($e->get_is_final()) {
                    // is int
                    if ($e->get_name() == "DH") {
                        $tape[] = "integer,{$token}";
                    }
                    // is decimal
                    else if ($e->get_name() == "DI") {
                        $tape[] = "decimal,{$token}";
                    }
                    // is id
                    else if ($e->get_name() == "DJ" or $e->get_name() == "DG") {
                        $id = null;
                        foreach ($symbol_table as $item){
                            if ($item["rotulo"] == $token){
                                $id = $item["id"];
                                break;
                            }
                        }

                        if ($id == null){
                            $id = count($symbol_table) + 1;

                            $symbol_table[] = [
                                "id" => $id,
                                "rotulo" => $token,
                                "linha" => $line
                            ];
                        }

                        $tape[] = "id,{$id}";
                    }
                    // is an error
                    else if ($e->get_name() == Configuration::get_err_rule_name()) {
                        $tape[] = Configuration::get_err_rule_name() . ",{$token},{$line}";
                        CommandLineHelper::print_magenta_message("Lexical error: Token {$token} on line {$line} was not recognized");
                    }
                    // is key word
                    else {

                        $tape[] = $e->get_name();
                    }
                }
                // is error
                else {
                    $tape[] = Configuration::get_err_rule_name() . ",{$token},{$line}";
                    CommandLineHelper::print_magenta_message("Lexical error: Token {$token} on line {$line} was not recognized");
                }
                $e = $grammar->get_rule_by_name(Configuration::get_init_rule_name());
                $next_rule_name = null;
                $token = null;
            } else {
                $token .= $character;
                $next_rule_name = $e->get_non_terminal_by_terminal($character);
                $e = $grammar->get_rule_by_name($next_rule_name) ?? $e;
            }
            $number_of_tokens_read++;
        }

        $tape[] = "$";
    }
}