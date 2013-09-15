<?php

namespace Ker\CRUD;

/**
 * Klasa abstrakcyjna realizująca interfejs CRUD.\n
 * Zapewnia on jednolite zarządzanie składowanymi rekordami w oparciu o bazę danych.\n
 * Korzysta z wzorca projektowego Property.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @date 2013-09-15 20:08:43
 * @abstract
 */
abstract class ADB extends \Ker\AProperty implements ICRUD
{

    /**
     * Nazwa tabeli w bazie danych.
     *
     * @static
     * @protected
     */
    protected static $table = "table";

    /**
     * Spis pól w tabeli.
     * Tablica o strukturze klucz=>wartość, gdzie klucz to identyfikator pola w systemie a wartość to nazwa kolumny w bazie.
     *
     * @static
     * @protected
     * @warning Wymagany jest klucz "PK" (pojedyńcze pole!).
     */
    protected static $fields = array(
        "PK" => "id",
    );

    /**
     * Metoda podmieniająca identyfikatory pól w systemie na odpowiadające im nazwy kolumn w bazie. Identyfikator pola rozpoznawany jest
     * przez otoczenie go znakiem "`". Każdy identyfikator musi istnieć jako klucz w tablicy $fields.
     *
     * @static
     * @protected
     * @param string zapytanie sql lub jego fragment przed podmienieniem pól
     * @return string zapytanie sql lub jego fragment po podmienieniu pól
     */
    protected static function transformSqlFields($_)
    {
        if ($_ === "") {
            return $_;
        }

        $fields = static::$fields;
        $subquery = preg_replace_callback(
                '/`(.+?)`/', function ($_) use ($fields) {
                    return '`' . $fields[$_[1]] . '`';
                }, $_
        );

        return $subquery;
    }

    /**
     * Metoda pobierająca pola.
     *
     * @public
     * @param array|list|string $... pola do pobrania: pojedyńcza nazwa, lista lub tablica nazw, jeśli nie podano żadnej - pobierze wszystkie
     * @return mixed|array element lub tablica elementów
     */
    public function get()
    {
        if (!func_num_args()) {
            return parent::get(array_keys(static::$fields));
        }

        if (func_num_args() === 1) {
            return parent::get(func_get_arg(0));
        }

        return parent::get(func_get_args());
    }

    /**
     * Metoda zapisująca pojedyńcze pole.
     *
     * @public
     * @param string $_name nazwa pola do zapisania
     * @param mixed $_value wartość pola
     */
    public function setOne($_name, $_value)
    {
        if (!isset(static::$fields[$_name])) {
            throw new \InvalidArgumentException("Argument out of allowed fields ($_name)");
        }

        $this->container[$_name] = $_value;
        $this->modified[$_name] = true;
    }

    /**
     * Metoda zapisująca pojedyńcze pole _bez_ oznaczania pola jako zmodyfikowanego.
     *
     * @public
     * @param string $_name nazwa pola do zapisania
     * @param mixed $_value wartość pola
     */
    public function setOneSilently($_name, $_value)
    {
        if (!isset(static::$fields[$_name])) {
            throw new \InvalidArgumentException("Argument out of allowed fields ($_name)");
        }

        $this->container[$_name] = $_value;
    }

}
