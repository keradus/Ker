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
     * Metoda wyświetlajaca lub zwracająca (zależnie od przekazanych parametrów) rozbudowanego dumpa z przekazanych parametrów.
     * Dump jest przygotowywany tylko, gdy w \Ker\Config klucz "debug" jest ustawiona na wartosc true.
     *
     * @static
     * @public
     * @param array $_opts [opt = null] tablica opcji sterujacych dumpem:\n
     *  trace => (bool) [opt = true] czy uwzględnić stos wywołań
     *  memory => (bool) [opt = true] czy uwzględnić zużycie pamięci
     *  print => (bool) [opt = true] czy wyświetlić dumpa
     * @param list $... elementy do zdumpowania
     * @return string|null tekstowa reprezentacja dumpa, zwracana tylko gdy $_opts["print"] === false
     */
    public static function dmp(/* opts, list */)
    {
        if (!\Ker\Config::getOne("debug")) {
            return;
        }

        $args = func_get_args();
        $_opts = array_shift($args);

        ob_start();

        $callPlace = debug_backtrace();

        // zapewniamy, by w przypadku wywołania metody z wrappera dump - poprawie wyświetlić informacje o miejscu wywołania dmp/dump
        if ($callPlace[1]["class"] === "Ker\Utils\Debug" && $callPlace[1]["function"] === "dump") {
            $callPlace = "{$callPlace[1]["file"]} :: {$callPlace[1]["line"]}";
        } else {
            $callPlace = "{$callPlace[0]["file"]} :: {$callPlace[0]["line"]}";
        }

        echo "<div style='text-align: left; border: red solid; margin: 5px; padding: 5px;'>";
        echo "<span style='color: red; font-weight: bold;'>DEBUG: $callPlace :</span>";
        call_user_func_array(array("static", "preDump"), $args);

        if (!isset($_opts["trace"]) || $_opts["trace"]) {
            echo "<hr />";
            static::trace();
        }

        if (!isset($_opts["memory"]) || $_opts["memory"]) {
            echo "<hr />";
            echo "Memory: ";
            static::memory();
            echo " / ";
            static::memory(true);
            echo "<hr />";
            echo "MemoryMax: ";
            static::memoryMax();
            echo " / ";
            static::memoryMax(true);
        }

        echo "</div>";

        if (!isset($_opts["print"]) || $_opts["print"]) {
            ob_end_flush();

            return;
        }

        $content = ob_get_clean();
        $content = preg_replace("/\n/", "<br />", $content);

        return preg_replace("/(?<=\s)\s/", "&nbsp;", $content);
    }

    /**
     * Metoda będąca shorthandem dla metody dmp, gdzie jako opcje sterujące przekazuje NULLa.
     *
     * @static
     * @public
     * @param list $... elementy do zdumpowania
     */
    public static function dump(/* list */)
    {
        return self::dmp(NULL, func_get_args());
    }

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
