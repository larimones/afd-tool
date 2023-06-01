<?php

namespace Services;

use Entities\Grammar;

class GrammarFactory
{
    private InputFileService $input_file_service;
    private GrammarMapper $grammar_mapper;
    private FiniteAutomatonService $finite_automaton_service;

    public function __construct(InputFileService $input_file_service, GrammarMapper $grammar_mapper, FiniteAutomatonService  $finite_automaton_service)
    {
        $this->input_file_service = $input_file_service;
        $this->grammar_mapper = $grammar_mapper;
        $this->finite_automaton_service = $finite_automaton_service;
    }

    public function createGrammar(string $grammar_path) : Grammar{

        $metadata = $this->input_file_service->get_and_validate_file_content($grammar_path);

        $tokens = $this->input_file_service->get_tokens_from_grammar_file($metadata);
        if (count($tokens) > 0) {
            $grammar_from_tokens = new Grammar();
            $this->grammar_mapper->from_tokens($grammar_from_tokens, $tokens);
        }

        $grammar_from_file_as_array = $this->input_file_service->get_grammar_from_grammar_file($metadata);
        if (count($grammar_from_file_as_array) > 0) {
            $grammar_from_file = new Grammar();
            $this->grammar_mapper->from_bnf_regular_grammar($grammar_from_file, $grammar_from_file_as_array);
        }

        $grammar = $this->grammar_mapper->unify_grammars($grammar_from_tokens, $grammar_from_file);

        $this->finite_automaton_service->transform_grammar_in_deterministic_finite_automaton($grammar);

        return $grammar;
    }
}