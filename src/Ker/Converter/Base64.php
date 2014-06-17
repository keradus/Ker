<?php

namespace Ker\Converter;

/**
 * Statyczna klasa kodująca dane algorytmem base64.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @static
 */
class Base64 extends \Ker\Converter
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
     * Metoda dekodująca.
     *
     * @static
     * @protected
     * @param string dane zakodowane
     * @return string dane odkodowane
     */
    protected static function decodeRaw($_data)
    {
        $result = base64_decode($_data);

        if ($result === false) {
            throw new \InvalidArgumentException("Base64 decode error");
        }

        return $result;
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
        $result = base64_encode($_data);

        if ($result === false) {
            throw new \InvalidArgumentException("Base64 encode error");
        }

        return $result;
    }

    /**
     * Metoda sprawdzajaca, czy podana wartość jest zakodowana.
     *
     * @static
     * @public
     * @param string tekst
     * @return bool wynik testu
     */
    public static function isEncoded($_data)
    {
        // sprawdzamy czy tekst ma długość równą wielokrotności 4ki
        if (strlen($_data) % 4 > 0) {
            return false;
        }

        return parent::isEncoded($_data);
    }
}
