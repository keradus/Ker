<?php

namespace Ker;

/**
 * Klasa abstrakcyjna realizująca interfejs CRUD.\n
 * Zapewnia on jednolite zarządzanie składowanymi rekordami w oparciu o bazę danych.\n
 * Korzysta z wzorca projektowego Property.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @date 2013-09-15 20:56:10
 * @abstract
 */
abstract class CRUD
{
    use \Ker\PropertyTrait;

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
     * @protected
     * @param string pole
     * @param int|string|array wartość lub możliwe wartości pola
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
     * @protected
     * @param string pole
     * @param int|string|array wartość lub możliwe wartości pola
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
     * Metoda usuwająca rekord.
     *
     * @static
     * @public
     * @param mixed klucz główny tabeli
     * @return int ilość usuniętych rekordów
     */
    public static function destroy($_ = NULL)
    {
        $sql = "DELETE FROM `" . static::$table . "` WHERE `" . static::$fields["PK"] . "` = :pk";

        return static::getDbHandler()->delete($sql, array(":pk" => $_));
    }

    /**
     * Metoda usuwająca wszystkie rekordy używając do tego komendy TRUNCATE.
     *
     * @static
     * @public
     */
    public static function truncate()
    {
        $sql = "TRUNCATE TABLE `" . static::$table . "`";

        static::getDbHandler()->call($sql);
    }

    /**
     * Metoda usuwająca wszystkie rekordy.
     *
     * @static
     * @public
     */
    public static function destroyAll()
    {
        $sql = "DELETE FROM `" . static::$table . "`";

        static::getDbHandler()->delete($sql);
    }

    /**
     * Metoda wytwórcza zwracająca tablicę obiektów klasy.
     *
     * @static
     * @public
     * @param $_ [opt = NULL] Tablica parametrów:\n
     *  fields => [opt] nazwy pól, które chcemy pobrać, domyślnie wszystkie pola generowane na podstawie static::$fields\n
     *  where => [opt] warunek WHERE dla zapytania (bez słowa kluczowego WHERE), decydujący o selekcji rekordów\n
     *  order => [opt] fragment ORDER BY dla zapytania (bez słów kluczowych ORDER BY), decydujący o kolejności rekordów\n
     *  limit => [opt] maksymalna ilość pobranych rekordów (bez słowa kluczowego LIMIT)
     *  countOnly => [opt] na wyjściu zamiast instancji obiektów otrzymamy ilość pasujących rekordów
     * @return array tablica utworzonych obiektów lub ich ilosc jesli ustawiono parametr countOnly
     */
    public static function factory($_ = NULL)
    {
        $query = static::buildSelect($_);
        $items = static::getDbHandler()->select($query);

        if (!empty($_["countOnly"])) {
            return count($items);
        }

        $calledClass = get_called_class();

        return array_map(function (& $item) use ($calledClass) {
                    $obj = new $calledClass(array("prepared" => $item));
                    $obj->isNew = false;
                    return $obj;
                }, $items);
    }

    /**
     * Metoda zwracająca handler do bazy danych.
     *
     * @static
     * @public
     * @return DB handler
     * @see Ker\Config::getOne("SQL")
     */

    public static function getDbHandler()
    {
        static $handler = NULL;

        if (!$handler) {
            $handler = \Ker\Config::getOne("SQL");
        }

        return $handler;
    }

    /**
     * Metoda pobierająca nazwę tabeli.
     *
     * @static
     * @public
     * @return string nazwa tabeli
     */
    public static function getTable()
    {
        return static::$table;
    }

    /**
     * Metoda pobierająca nazwy kolumn w tabeli.
     *
     * @public
     * @param array|list|string $... nazwy kolumn do pobrania: pojedyńcza nazwa, lista lub tablica nazw
     * @return mixed|array nazwa kolumny lub ich tablica
     */
    public static function getFields(/* list of arrays */)
    {
        $func_num_args = func_num_args();

        if (!$func_num_args) {
            throw new \BadMethodCallException("Parameter missing");
        } elseif ($func_num_args === 1) {
            $name = func_get_arg(0);
            if (is_array($name)) {
                $return = array();
                foreach ($name AS $item) {
                    $return[$item] = static::$fields[$item];
                }

                return $return;
            }

            return static::$fields[$name];
        }

        $return = array();
        foreach (func_get_args() AS $item) {
            $return[$item] = static::$fields[$item];
        }

        return $return;
    }

    /**
     * Flaga oznaczająca czy rekord jest nowy.
     *
     * @protected
     */
    public $isNew;

    /**
     * Tablica zawierająca listę zmodyfikowanych pól.
     *
     * @protected
     */
    protected $modified;

    /**
     * Konstruktor.
     *
     * @public
     * @param $_ [opt = NULL] Brak parametru oznacza tworzenie nowego obiektu, parametr to skalar oznaczajacy ID inicjalizowanego obiektu lub tablica parametrów:\n
     *  loadPk => [opt] ładuje obiekt o zadanym PK, jeśli nie podano parametru - tworzy nowy rekord\n
     *  load_pk => [opt] alias dla loadPk\n
     *  prepared => [opt] uzupełnia pola na podstawie otrzymanej tablicy, nie oznacza pól w $modified
     * @return Ker\CRUD instancja klasy dziedziczącej
     * @exception Ker\Ex\NoData - wyjątek rzucany w sytuacji, gdy zlecono załadowanie nieistniejącego obiektu
     */
    public function __construct($_ = NULL)
    {
        $this->isNew = true;

        if (!$_) {
            return;
        }

        $fields = array();

        if (!is_array($_)) {
            $_ = array("loadPk" => $_);
        }

        $pk = (isset($_["loadPk"])
            ? $_["loadPk"]
            : (isset($_["load_pk"])
                ? $_["load_pk"]
                : null
            )
        );

        if ($pk) {
            $this->isNew = false;

            $queryFields = array_keys(static::$fields);
            foreach ($queryFields AS & $field) {
                $field = "`$field` AS '$field'";
            }

            $sql = static::transformSqlFields("SELECT " . implode(", ", $queryFields))
                    . " FROM `" . static::$table . "` WHERE `" . static::$fields["PK"] . "` = :pk";
            $fields = static::getDbHandler()->selectOne($sql, array(":pk" => $pk));

            if (!$fields) {
                throw new \Ker\CRUD\NoDataException("Item not exists");
            }
        }

        if (isset($_["prepared"]) AND is_array($_["prepared"])) {
            $fields = array_merge($fields, $_["prepared"]);
        }

        if ($fields) {
            foreach ($fields AS $key => $value) {
                $this->setOneSilently($key, $value);
            }
        }
    }

    /**
     * Metoda usuwająca rekord.
     *
     * @public
     * @return int ilość usuniętych rekordów, uwaga - jeśli zlecimy usunięcie nowego, niezapisanego rekordu - otrzymamy 0, które nie będzie jednak błędem
     */
    public function delete($_ = NULL)
    {
        if ($this->isNew || !$this->hasOne("PK")) {
            return 0;
        }

        $sql = "DELETE FROM `" . static::$table . "` WHERE `" . static::$fields["PK"] . "` = :pk";

        return static::getDbHandler()->deleteOne($sql, array(":pk" => $this->getOne("PK")));
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
     * Metoda zapisująca rekord.\n
     * W przypadku zapisywania nowego rekordu aktualizowana jest informacja o PK (bez zmian w $modified).
     *
     * @public
     * @return mixed identyfikator zapisanego obiektu
     */
    public function save($_ = NULL)
    {
        $sql = NULL;
        $params = array();

        $fields = $this->modified;
        foreach ($fields AS $key => & $value) {
            $value = "`" . static::$fields[$key] . "` = :$key";
            $params[":$key"] = $this->getOne($key);
        }

        if ($this->isNew) {
            $sql = "INSERT INTO `" . static::$table . "` SET " . implode(", ", $fields);
            $pk = static::getDbHandler()->insert($sql, $params);
            $this->setOneSilently("PK", $pk);
            $this->isNew = false;

            $this->modified = [];

            return $pk;
        }

        $params[":pk"] = $this->getOne("PK");
        $sql = "UPDATE `" . static::$table . "` SET " . implode(", ", $fields) . " WHERE `" . static::$fields["PK"] . "` = :pk";
        static::getDbHandler()->updateOne($sql, $params);

        $this->modified = [];

        return $this->getOne("PK");
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
