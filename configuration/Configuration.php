<?php

namespace Configuration;

class Configuration
{
    /**
     * @var string
     */

    public function __construct()
    {
    }

    /**
     * @return string
     */
    public static function get_err_rule_name(): string
    {
        return $_ENV["err_rule_name"];
    }

    /**
     * @return string
     */
    public static function get_empty_transition_symbol(): string
    {
        return $_ENV["empty_transition_symbol"];
    }

    /**
     * @return string
     */
    public static function get_unreachable_rule_symbol(): string
    {
        return $_ENV["unreachable_rule_symbol"];
    }

    /**
     * @return string
     */
    public static function get_dead_rule_symbol(): string
    {
        return $_ENV["dead_rule_symbol"];
    }

    /**
     * @return string
     */
    public static function get_final_rule_symbol(): string
    {
        return $_ENV["final_rule_symbol"];
    }

    /**
     * @return string
     */
    public static function get_init_rule_symbol(): string
    {
        return $_ENV["init_rule_symbol"];
    }

}