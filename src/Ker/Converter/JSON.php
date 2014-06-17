<?php

namespace Ker\Converter;

/**
 * Statyczna klasa kodująca dane algorytmem JSON.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @static
 */
class JSON extends \Ker\Converter
{
    /**
     * Kontener na cache kodowanych danych.
     *
     * @type array
     * @static
     * @protected
     */
    protected static $encodeCache = array();

    /**
     * Kontener na cache dekodowanych danych.
     *
     * @type array
     * @static
     * @protected
     */
    protected static $decodeCache = array();

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
        $result = json_decode($_data, true);
        $error = json_last_error();

        if ($error !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException("JSON decode error: " . $error);
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
        $result = json_encode($_data);
        $error = json_last_error();

        if ($error !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException("JSON encode error: " . $error);
        }

        return $result;
    }
}
