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
