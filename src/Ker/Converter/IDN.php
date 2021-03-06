<?php

namespace Ker\Converter;

/**
 * Statyczna klasa kodująca nazwy domen IDN/ASCII.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @static
 */
class IDN extends \Ker\Converter
{
    /**
     * Kontener na cache kodowanych danych.
     *
     * @type array
     * @static
     * @protected
     */
    protected static $encodeCache = [];

    /**
     * Kontener na cache dekodowanych danych.
     *
     * @type array
     * @static
     * @protected
     */
    protected static $decodeCache = [];

    /**
     * Metoda wyliczająca skrót danych. Metoda służy do określenia klucza cache'owania.
     *
     * @static
     * @public
     * @param string tekst
     * @return string skrót tekstu
     */
    protected static function computeHash($_data)
    {
        return $_data;
    }

    /**
     * Metoda dekodująca.
     *
     * @static
     * @protected
     * @param string dane zakodowane
     * @return string dane odkodowane
     */
    protected static function decodeRaw($_data)
    {
        return idn_to_utf8($_data);
    }

    /**
     * Metoda kodująca.
     *
     * @static
     * @protected
     * @param string dane do zakodowania
     * @return string dane zakodowane
     */
    protected static function encodeRaw($_data)
    {
        return idn_to_ascii($_data);
    }
}
