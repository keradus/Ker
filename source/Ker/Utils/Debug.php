<?php

namespace Ker\Utils;

/**
 * Klasa ze statycznymi metodami użytkowymi służącymi celom debugowym.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @date 2013-09-15 19:46:30
 */
class Debug
{

    /**
     * Metoda wyświetlająca dumpa przekazanych elementów używając do tego metody print_r, owijając każdy z nich tagiem <pre>.
     *
     * @static
     * @public
     * @param list $... elementy do zdumpowania
     */
    public static function prePrint(/* list */)
    {
        foreach (func_get_args() AS $data) {
            echo "<pre style='border: blue solid 1px;'>", print_r($data, true), "</pre>";
        }
    }

    /**
     * Metoda wyświetlająca dumpa przekazanych elementów, owijając każdy z nich tagiem <pre>.
     *
     * @static
     * @public
     * @param list $... elementy do zdumpowania
     */
    public static function preDump(/* list */)
    {
        foreach (func_get_args() AS $data) {
            echo "<pre style='border: blue solid 1px;'>", var_dump($data), "</pre>";
        }
    }

    /**
     * Metoda wyświetlająca stos wywołań.
     *
     * @static
     * @public
     */
    public static function trace()
    {
        echo "<pre>";
        debug_print_backtrace();
        echo "</pre>";
    }

    /**
     * Metoda wyświetlająca ilość zaalokowanej pamięci.
     *
     * @static
     * @public
     * @param bool [opt = false] $_real czy uwzględnić również pamięc zaalokowaną nie przy użyciu emalloc
     */
    public static function memory($_real = false)
    {
        echo memory_get_usage($_real);
    }

    /**
     * Metoda wyświetlająca szczytową ilość zaalokowanej pamięci.
     *
     * @static
     * @public
     * @param bool [opt = false] $_real czy uwzględnić również pamięc zaalokowaną nie przy użyciu emalloc
     */
    public static function memoryMax($_real = false)
    {
        echo memory_get_peak_usage($_real);
    }

}
