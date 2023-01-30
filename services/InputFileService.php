<?php

namespace Services;

use Helpers\CommandLineHelper;
use Helpers\StringHelper;

class InputFileService
{
    /**
     * @param string $path
     * @return array
     */
    public static function get_and_validate_grammar_file(string $path) : array
    {
        @$file = file_get_contents($path);

        if ($file === false) {
            CommandLineHelper::print_magenta_message("Erro ao ler arquivo informado em --grammar: '$path'");
            exit(1);
        }

        return array_filter(explode("\n", $file));
    }

    /**
     * @param array $file_data
     * @return array
     */
    public static function get_tokens_from_grammar_file(array $file_data): array
    {
        $tokens = [];

        foreach ($file_data as $value) {
            if (!StringHelper::contains($value, "::=")) {
                $tokens[] = $value;
            }
        }

        return $tokens;
    }

    /**
     * @param array $file_data
     * @return array
     */
    public static function get_grammar_from_grammar_file(array $file_data) : array
    {
        $ra_rules = [];

        foreach ($file_data as $value) {
            if (StringHelper::contains($value, "::=")) {
                $ra_rules[] = $value;
            }
        }

        return $ra_rules;
    }

}