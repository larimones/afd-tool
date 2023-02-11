<?php

namespace Services;

use Configuration\Configuration;
use Entities\Grammar;

class PrintService
{

    /**
     * @param Grammar $grammar
     * @return void
     */
    public static function grammar_to_cmd(Grammar $grammar): void
    {
        $terminals = $grammar->get_all_terminals();

        foreach ($grammar->get_rules() as $rule) {
            if ($rule->get_is_final() == true) {
                print(Configuration::get_final_rule_symbol());
            }
            if ($rule->get_is_initial() == true) {
                print(Configuration::get_init_rule_symbol());
            }
            if ($rule->is_dead() == true) {
                print(Configuration::get_dead_rule_symbol());
            }
            if (json_encode($rule->get_is_reachable()) == "false") {
                print(Configuration::get_unreachable_rule_symbol());
            }
            print(" {$rule->get_name()} |");
            foreach ($rule->get_non_terminals_by_terminals($terminals) as $non_terminals_by_terminal) {
                print(" " . key($non_terminals_by_terminal) . " => { ");
                foreach ($non_terminals_by_terminal as $rules) {
                    foreach ($rules as $rule) {
                        print($rule);
                    }
                }
                print(" } ");
            }
            print("\n");
        }
    }

    /**
     * @param array $matrix
     * @param string $file_name
     * @param string $title
     * @return void
     */
    public static function matrix_to_file(array $matrix, string $file_name, string $title): void
    {
        $err_rule_symbol = Configuration::get_err_rule_name();
        $init_rule_symbol = Configuration::get_init_rule_symbol();
        $final_rule_symbol = Configuration::get_final_rule_symbol();
        $dead_rule_symbol = Configuration::get_dead_rule_symbol();
        $unreachable_rule_symbol = Configuration::get_unreachable_rule_symbol();

        $fp = fopen("{$file_name}.html", 'w');
        fwrite($fp, "<html><head><meta charset='UTF-8'></head><body>");
        fwrite($fp, "<table style='text-align: center; margin:auto;'><tr><td>Universidade Federal Da Fronteira Sul</td></tr><tr><td>Componente Curricular: Linguagens formais e autômatos</td></tr><tr><td>Professor(a):	Braulio Adriano de Mello</td></tr><tr><td>Acadêmicos(as): Larissa Mones Bedin e Matheus Vieira Santos</td></tr><tr><td>Curso: Ciência Da Computação</td></tr></table>");
        fwrite($fp, "<br />");
        fwrite($fp, "<h2 style='text-align: center; margin:auto;'>{$title}</h2>");
        fwrite($fp, "<br />");
        fwrite($fp, "<table style='text-align: center; margin:auto; border-collapse: collapse;' >");
        foreach ($matrix as $row) {
            fwrite($fp, "<tr>");
            foreach ($row as $col) {
                $index = array_search($col, $row);
                $style = ($index == 0) ? 'border-style:none;': "border: 1px solid black; border-collapse: collapse;";
                fwrite($fp, "<td width='100px' style='{$style}'>{$col}</td>");
            }
            fwrite($fp, "</tr>");
        }
        fwrite($fp, "</table>");
        fwrite($fp, "<br />");
        fwrite($fp, "<table border='1' style='text-align: center; margin:auto; border: 1px solid black; border-collapse: collapse;' >    <tr>        <td colspan='2'>Legenda</td>    </tr>    <tr>        <td width='50px'>{$init_rule_symbol}</td>        <td width='200px'>Estado Inicial</td>    </tr>    <tr>        <td>{$final_rule_symbol}</td>        <td>Estado Final</td>    </tr>    <tr>        <td>{$unreachable_rule_symbol}</td>        <td>Estado Inalcançável</td>    </tr>    <tr>        <td>{$dead_rule_symbol}</td>        <td>Estado Morto</td>    </tr><tr>        <td>{$err_rule_symbol}</td>        <td>Estado de Erro</td>    </tr></table>");
        fwrite($fp, "</body></html>");

        fclose($fp);
    }

    /**
     * @param Grammar $grammar
     * @return array
     */
    public static function from_grammar_to_matrix(Grammar $grammar): array
    {
        $terminals = $grammar->get_all_terminals();
        $rules = $grammar->get_rules();

        $matrix[0][0] = '';
        $matrix[0][1] = 'δ';

        $i = 2;
        foreach ($terminals as $terminal) {
            $matrix[0][$i] = $terminal;
            $i++;
        }

        $i = 1;
        foreach ($rules as $rule) {
            $characteristics = [];

            if (json_encode($rule->get_is_reachable()) == "false") {
                $characteristics[] = Configuration::get_unreachable_rule_symbol();
            }

            if ($rule->is_dead()) {
                $characteristics[] = Configuration::get_dead_rule_symbol();
            }

            if ($rule->get_is_final()) {
                $characteristics[] = Configuration::get_final_rule_symbol();
            }

            if ($rule->get_is_initial()) {
                $characteristics[] = Configuration::get_init_rule_symbol();
            }

            $matrix[$i][0] = join(" ", $characteristics);
            $matrix[$i][1] = $rule->get_name();

            $transitions = $rule->get_non_terminals_by_terminals($terminals);

            $j = 2;
            foreach ($transitions as $transition) {

                foreach ($transition as $next_rules) {
                    $rules = implode('', $next_rules);
                }
                $matrix[$i][$j] = $rules;
                $j++;
            }
            $i++;
        }

        return $matrix;
    }
}