<?php

namespace Ker;

/**
 * Klasa implementująca wzorzec projektowy `Property`. Służy do zarządzania zasobami tekstowymi (i funkcyjnymi generującymi tekst).
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:56:44
 */
class Res
{
    use \Ker\StaticPropertyTrait;

    /**
     * Metoda pobierająca wynik funkcji lambda.
     *
     * @public
     * @param  string $_nazwa nazwa zasobu
     * @param  mixed  $...    opcjonalne parametry przekazane do funkcji lambda jesli wywolano zasob funkcyjny
     * @return string wyznaczona wartość zasobu
     */
    public static function call($_name)
    {
        $res = static::getOne($_name);

        // podwojne sprawdzenie - nie zawsze callback jest Closure, kwestia wersji php
        if (($res instanceof \Closure) || is_callable($res)) {
            $args = func_get_args();
            array_shift($args);

            return call_user_func_array($res, $args);
        }

        return $res;
    }

    /**
     * Metoda pobierająca pojedyńczy element.
     * Rzuca wyjatek gdy element nie zostanie odnaleziony.
     *
     * @static
     * @public
     * @param  string $_name nazwa elementu do pobrania
     * @return mixed  element
     */
    public static function getOne($_name, $_value = null)
    {
        if (!parent::hasOne($_name)) {
            throw new \LogicException("Key '$_name' not found!");
        }

        return parent::getOne($_name, $_value);
    }
}
