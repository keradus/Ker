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
     * Metoda pomocnicza dla metody buildWhere budująca fragment sekcji zapytania WHERE w sposób niestandardowy lub dla nieistniejących pól.
     * Domyślnie metoda jest pusta, w przypadku chęci wykorzystania możliwości tej metody należy ją przesłonić w klasie potomnej.
     *
     * @static
     * @public
     * @param string pole
     * @param string|array wartość lub możliwe wartości pola
     * @return string fragment zapytania sql stanowiący wartość sekcji WHERE
     */
    protected static function buildWhere_extraField($_field, $_value)
    {
        return NULL;
    }

    /**
     * Metoda pomocnicza dla metody buildWhere budująca fragment sekcji zapytania WHERE dla istniejących pól.
     * Gdy jako wartość podamy skalara to w zapytaniu otrzymamy proste przyrównanie wartości, gdy tablicę - sprawdzimy czy przekazane pole
     * jest jedną z wartości.
     *
     * @static
     * @public
     * @param string pole
     * @param string|array wartość lub możliwe wartości pola
     * @return string fragment zapytania sql stanowiący wartość sekcji WHERE
     */
    protected static function buildWhere_standardField($_field, $_value)
    {
        if (!isset(static::$fields[$_field])) {
            return NULL;
        }

        if (is_array($_value)) {
            if (empty($_value)) {
                return NULL;
            }

            return "`$_field` IN (" . implode(", ", array_map(function ($_) {
                                        return "'$_'";
                                    }, $_value)) . ")";
        }

        return "`$_field` = '$_value'";
    }

    /**
     * Metoda składająca przekazane parametry w wartość sekcji WHERE zapytania SQL.
     *
     * W przypadku otrzymania napisu po prostu go zwraca.
     * Jeśli otrzyma tablicę to przekształca ją w kwerende SQL. Przekształcenie pary klucz=>wartość na fragment SQL odbywa się w następującej
     * kolejności:
     * - buildWhere_extraField - obsługę nietypowych warunków lub pól,
     * - buildWhere_standardField - domyślna obsługa istniejących pól.
     *
     * @static
     * @public
     * @param array $_ Tablica parametrów:\n
     *  where => (string|array) [opt] surowy fragment zapytania SQL lub tablica mapująca nazwę pola na jego wartość lub tablicę wartości\n
     *  whereGlue => (string) [opt] łącznik poszczególnych fragmentów wygenerowanych na bazie $_["where"] (gdy to tablica)
     * @return string fragment zapytania sql stanowiący wartość sekcji WHERE
     */
    public static function buildWhere($_)
    {
        if (empty($_["where"])) {
            return NULL;
        }

        $where = $_["where"];

        if (is_string($where)) {
            return $where;
        }

        $ret = array();

        foreach ($where AS $k => $v) {
            $tmp = static::buildWhere_extraField($k, $v);
            if ($tmp !== NULL) {
                $ret[] = $tmp;
                continue;
            }

            $tmp = static::buildWhere_standardField($k, $v);
            if ($tmp !== NULL) {
                $ret[] = $tmp;
                continue;
            }

            throw new \LogicException("Can't process sql-where param: $k => $v");
        }

        return implode(" " . (isset($_["whereGlue"]) ? $_["whereGlue"] : "AND") . " ", $ret);
    }

    /**
     * Metoda budująca zapytanie do bazy.
     *
     * @static
     * @protected
     * @param array $_ Tablica parametrów:\n
     *  fields => (array) [opt] nazwy pól, które chcemy pobrać, domyślnie brane wszystkie\n
     *  where => (string|array) [opt] warunek WHERE dla zapytania (bez słowa kluczowego WHERE), decydujący o selekcji rekordów\n
     *  whereGlue => (string) [opt] łącznik poszczególnych fragmentów wygenerowanych na bazie $_["where"] (gdy to tablica)\n
     *  order => (string) [opt] fragment ORDER BY dla zapytania (bez słów kluczowych ORDER BY), decydujący o kolejności rekordów\n
     *  limit => (limit) [opt] maksymalna ilość pobranych rekordów (bez słowa kluczowego LIMIT)
     */
    protected static function buildSelect($_)
    {
        $queryFields = (isset($_["fields"]) ? $_["fields"] : array_keys(static::$fields));
        foreach ($queryFields AS & $field) {
            $field = "`$field` AS '$field'";
        }

        return static::transformSqlFields("SELECT " . implode(", ", $queryFields))
                . " FROM `" . static::$table . "`"
                . static::transformSqlFields(
                        (!empty($_["where"]) ? " WHERE " . static::buildWhere($_) : "")
                        . (!empty($_["order"]) ? " ORDER BY {$_["order"]}" : "")
                        . (!empty($_["limit"]) ? " LIMIT {$_["limit"]}" : "")
        );
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
