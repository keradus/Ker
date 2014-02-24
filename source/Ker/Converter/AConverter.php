<?php

namespace Ker\Converter;

/**
 * Statyczna klasa abstrakcyjna dostarczająca podstawową funkcjonalność konwertera danych.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @static
 * @abstract
 */
abstract class AConverter
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
     * Domyślnie zwraca dane w formie niezmienionej, jednak w sytuacji gdy konwertowane dane mogą być obszerne należy przesłonić metodę
     * zapewniając krótki format klucza.
     *
     * @static
     * @protected
     * @param string dane
     * @return string skrót danych
     */
    protected static function computeHash($_data)
    {
        return $_data;
    }

    /**
     * Metoda dekodująca.
     *
     * @static
     * @public
     * @param string dane zakodowane
     * @return string dane odkodowane
     */
    public static function decode($_data)
    {
        $hash = static::computeHash($_data);

        if (!array_key_exists($hash, static::$decodeCache)) {
            static::$decodeCache[$hash] = static::decodeRaw($_data);
        }

        return static::$decodeCache[$hash];
    }

    /**
     * Metoda dekodująca.
     *
     * @abstract
     * @static
     * @protected
     * @param string dane zakodowane
     * @return string dane odkodowane
     */
    protected static function decodeRaw($_data)
    {
        throw new \BadMethodCallException("method not implemented");
    }

    /**
     * Metoda kodująca.
     * Cacheuje kodowane dane tak, by te same dane były kodowane tylko raz.
     *
     * @static
     * @public
     * @param string dane do zakodowania
     * @return string dane zakodowane
     */
    public static function encode($_data)
    {
        $hash = static::computeHash($_data);

        if (!array_key_exists($hash, static::$encodeCache)) {
            static::$encodeCache[$hash] = static::encodeRaw($_data);
        }

        return static::$encodeCache[$hash];
    }

    /**
     * Metoda kodująca.
     *
     * @abstract
     * @static
     * @protected
     * @param string dane do zakodowania
     * @return string dane zakodowane
     */
    protected static function encodeRaw($_data)
    {
        throw new \BadMethodCallException("method not implemented");
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
        // definitywny test to odkodowanie i ponowne zakodowanie - jeśli na wyjściu otrzymamy pierwotne dane to są one poprawnie zakodowane
        try {
            return (static::encode(static::decode($_data)) === $_data);
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }
}
