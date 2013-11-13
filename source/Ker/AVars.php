<?php

namespace Ker;

/**
 * Klasa abstrakcyjna służąca do obsługi obsługi zmiennych zarządzanych przez serwer.
 * Korzysta z wzorca projektowego Property w wersji statycznej.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @date 2013-11-13 12:03:00
 * @abstract
 */
abstract class AVars extends APropertyStatic
{

    /**
     * Kontener na dane.
     *
     * @static
     * @protected
     */
    protected static $container;

    /**
     * Metoda sprawdzająca czy kontener zawiera jakiekolwiek elementy.
     *
     * @static
     * @public
     * @return bool informacja, czy kontener zawiera jakiekolwie elementy
     */
    public static function inUse()
    {
        return !empty(static::$container);
    }

    /**
     * Metoda pobierająca pojedyńczy element. Dokonuje przy tym html-encodowania.
     *
     * @static
     * @public
     * @param string $_name nazwa elementu do pobrania, jeśli element nie istnieje zwracany jest pusty string
     * @return mixed element
     */
    public static function getOne($_name)
    {
        $ret = parent::getOne($_name);

        return $ret ? htmlspecialchars($ret) : "";
    }

    /**
     * Metoda pobierająca pojedyńczy element. Nie dokonuje jednak przy tym html-encodowania.
     *
     * @static
     * @public
     * @param string $_name nazwa elementu do pobrania, jeśli element nie istnieje zwracany jest pusty string
     * @return mixed element
     */
    public static function getOneRaw($_name)
    {
        $ret = parent::getOne($_name);

        return $ret ? $ret : "";
    }

    /**
     * Metoda pobierająca elementy przy jednoczesnym ich html-encodowaniu.
     * Robi dokładnie to co metoda get, jednak została dla kompatybilności wstecznej. Nie należy więc używać tej metody - została jedynie dla
     * kompatybilności wstecznej i z kolejnymi wersjami zostanie usunięta.
     *
     * @static
     * @public
     * @param array|list|string $... elementy do pobrania: pojedyńcza nazwa, lista lub tablica nazw
     * @return mixed|array element lub tablica elementów
     * @deprecated
     */
    public static function getHtmlEncoded()
    {
        return forward_static_call_array(array("static", "get"), func_get_args());
    }

    /**
     * Metoda ustalajaca kontener.
     *
     * @static
     * @public
     * @param &array $_array referencja do tablicy, która ma się stać kontenerem
     */
    public static function setContainer(& $_array)
    {
        static::$container = & $_array;
    }

}
