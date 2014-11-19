<?php

namespace Ker\Utils;

/**
 * Klasa ze statycznymi metodami obróbki tekstu.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:35:01
 */
class Text
{

    /**
     * Funkcja enkoduje znaki html na tablicy wartości.
     *
     * @param  string $_valueArray     tablica z wartościami do zakodowania
     * @param  string $_exceptionArray [opt = array ( )] tablica wyjątków - kluczy z $_valueArray, które mają nie być enkodowane
     * @return array  tablica po zaenkodowaniu
     */
    public static function htmlSpecialCharsArray($_valueArray, $_exceptionArray = array())
    {
        foreach ($_valueArray as $k => $v) {
            if (!in_array($k, $_exceptionArray)) {
                $_valueArray[$k] = htmlspecialchars($v, ENT_QUOTES);
            }
        }

        return $_valueArray;
    }

    /**
     * Funkcja enkoduje znaki html na pojedyńczym napisie lub tablicy o dowolnym stopniu zagłębienia.
     *
     * @param  array|string $_value napis lub tablica z wartościami do zakodowania
     * @return array|string element o strukturze zgodnej z wejściową, jednak z zaenkodowanymi wartościami
     */
    public static function htmlSpecialCharsDeep($_value)
    {
        return ( is_array($_value) ? array_map('Utils\Text::htmlSpecialCharsDeep', $_value) : htmlspecialchars($_value, ENT_QUOTES) );
    }

    /**
     * Funkcja zamienia białe znaki poprzedzone pojedyńczą literą na twarde spacje.
     *
     * @param  string $_value tekst
     * @return string tekst po modyfikacji
     */
    public static function replaceEndingSpaceWithNbsp($_value)
    {
        return preg_replace("/ ([iwaozIWAOZ]) /", " $1&nbsp;", $_value);
    }

    /**
     * Funkcja usuwa z tekstu znaki nowej linii - tj \r i \n (oraz ich kombinacje).
     *
     * @param  string $_value tekst
     * @return string tekst po modyfikacji
     */
    public static function removeNewLines($_value, $_options = array())
    {
        $text = strtr($_value, array("\r" => "", "\n" => ""));

        if (!empty($_options["safeJsInline"])) {
            $text = strtr($text, array("//<![CDATA[" => "//<![CDATA[\n", "//]]>" => "//]]>\n"));
        }

        return $text;
    }

    /**
     * Funkcja usuwa ukościni zabezpieczające cudzysłów na pojedyńczym napisie lub tablicy o dowolnym stopniu zagłębienia.
     *
     * @param  array|string $_value napis lub tablica z wartościami do przetworzenia
     * @return array|string element o strukturze zgodnej z wejściową, jednak już przetworzona
     */
    public static function stripSlashesDeep($_value)
    {
        return ( is_array($_value) ? array_map('Utils\Text::stripSlashesDeep', $_value) : stripslashes($_value) );
    }

    /**
     * Funkcja usuwa tagi html na pojedyńczym napisie lub tablicy o dowolnym stopniu zagłębienia.
     *
     * @param  array|string $_value napis lub tablica z wartościami do przetworzenia
     * @return array|string element o strukturze zgodnej z wejściową, jednak już przetworzona
     */
    public static function stripTagsDeep($_value)
    {
        return ( is_array($_value) ? array_map('Utils\Text::stripTagsDeep', $_value) : strip_tags($_value) );
    }

}
