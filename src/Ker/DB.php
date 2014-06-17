<?php

namespace Ker;

/**
 * Klasa służąca do komunikacji z baza danych. Implementuje wzorzec projektowy adapter dla klasy PDO.
 * Zapewnia uproszczony interfejs dla najczęstszych akcji.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @date 2013-09-15 21:49:15
 */
class DB
{

    /**
     * Tablica z identyfikatorami sterowników baz danych obsługujących SAVEPOINTy.
     *
     * @static
     * @protected
     */
    protected static $savepointTransactions = array("mysql", "pgsql",);

    /**
     * Hash definiujacy wyświetlane informacje debugowe.
     * Klucze reprezentują odpowiadające im typy zapytań, specjalny klucz "all" wymusza wyświetlenie wszystkich debugów.
     *
     * @protected
     */
    protected $debug = array(
        "all" => false,
        "call" => false,
        "delete" => false,
        "insert" => false,
        "select" => false,
        "transaction" => false,
        "update" => false,
    );

    /**
     * Instancja klasy PDO.
     *
     * @protected
     */
    protected $instance;

    /**
     * Flaga informująca o dostępności zagnieżdzonych transakcji dla połączenia.
     *
     * @protected
     */
    protected $transactionNestable;

    /**
     * Poziom zagnieżdzenia transakcji. Zero w przypadku braku otwartej transakcji.
     *
     * @protected
     */
    protected $transactionLevel = 0;

    /**
     * Konstruktor klasy. Nawiązuje połączenie z bazą danych, ustawia tryb zgłaszania błędów na rzucanie wyjątków oraz kodowanie połączenia na UTF-8.
     *
     * @public
     * @param DBConfig $_config konfiguracja połączenia z bazą danych
     */
    public function __construct(\Ker\DBConfig $_config)
    {
        $this->instance = new \PDO($_config->dsn . ":host=" . $_config->host . ";dbname=" . $_config->database . ";port=" . $_config->port, $_config->user, $_config->password);
        $this->instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->instance->query("SET NAMES UTF8");
        $this->transactionNestable = in_array($_config->dsn, self::$savepointTransactions);
    }

    /**
     * Destruktor klasy, zwalnia instancje PDO.
     *
     * @public
     */
    public function __destruct()
    {
        unset($this->instance);
    }

    /**
     * Metoda wywołująca procedurę składowaną.
     *
     * @public
     * @param string $_sql zapytanie SQL do wykonania
     * @param array [opt = array ( )] $_value parametry dla zapytania SQL
     */
    public function call($_sql, $_params = array())
    {
        $this->showDebug("call", $_sql, $_params);

        $statement = $this->instance->prepare($_sql);
        $statement->execute($_params);
        $statement->closeCursor();
    }

    /**
     * Metoda dokonująca kasacji rekordów.
     *
     * @public
     * @param string $_sql zapytanie SQL do wykonania
     * @param array [opt = array ( )] $_value parametry dla zapytania SQL
     * @return int ilość usuniętych rekordów
     */
    public function delete($_sql, $_params = array())
    {
        $this->showDebug("delete", $_sql, $_params);

        $statement = $this->instance->prepare($_sql);
        $statement->execute($_params);
        $return = $statement->rowCount();
        $statement->closeCursor();

        return $return;
    }

    /**
     * Metoda dokonująca kasacji pojedyńczego rekordu. Do przekazanego zapytania dodaje fragment " LIMIT 1".
     *
     * @public
     * @param string $_sql zapytanie SQL do wykonania
     * @param array [opt = array ( )] $_value parametry dla zapytania SQL
     * @return int ilość usuniętych rekordów
     */
    public function deleteOne($_sql, $_params = array())
    {
        return self::delete("$_sql LIMIT 1", $_params);
    }

    /**
     * Metoda zabezpieczająca wartości przed wysłaniem ich jako część zapytania SQL. Wykorzystywana gdy wartości nie da się przesłać jako parametrów.
     *
     * @public
     * @param string|array $_value wartość/tablica wartości, które mają być zabezpieczona
     * @return string|array zabezpieczona wartość/tablica wartości
     */
    public function escape($_value)
    {
        if (is_array($_value)) {
            foreach ($_value AS $key => $val) {
                $_value[$key] = substr($this->instance->quote($val), 1, -1);
            }

            return $_value;
        }

        return substr($this->instance->quote($_value), 1, -1);
    }

    /**
     * Metoda dodająca rekord.
     *
     * @public
     * @param string $_sql zapytanie SQL do wykonania
     * @param array [opt = array ( )] $_value parametry dla zapytania SQL
     * @return int ID wprowadzonego rekordu
     */
    public function insert($_sql, $_params)
    {
        $this->showDebug("insert", $_sql, $_params);

        $statement = $this->instance->prepare($_sql);
        $statement->execute($_params);
        $statement->closeCursor();

        return $this->instance->lastInsertId();
    }

    /**
     * Metoda pobierająca informację czy zagnieżdżone transakcje są wspierane dla otwartego połączenia z bazą.
     *
     * @public
     * @return bool informacja czy zagnieżdżone transakcje są wspierane
     */
    public function isNestableTransactionSupported()
    {
        return $this->transactionNestable;
    }

    /**
     * Metoda pobierająca rekordy.
     *
     * @public
     * @param string $_sql zapytanie SQL do wykonania
     * @param array [opt = array ( )] $_value parametry dla zapytania SQL
     * @return array tablica tablic asocjacyjnych z pobranymi rekordami
     */
    public function select($_sql, $_params = array())
    {
        $this->showDebug("select", $_sql, $_params);

        $statement = $this->instance->prepare($_sql);
        $statement->execute($_params);
        $return = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return $return;
    }

    /**
     * Metoda pobierająca pojedyńczy rekord. Do przekazanego zapytania dodaje fragment " LIMIT 1".
     *
     * @public
     * @param string $_sql zapytanie SQL do wykonania
     * @param array [opt = array ( )] $_value parametry dla zapytania SQL
     * @return array|NULL tablica asocjacyjna z wartościami pobranego rekordu lub NULL w przypadku braku pasującego rekordu
     */
    public function selectOne($_sql, $_params = array())
    {
        $return = self::select("$_sql LIMIT 1", $_params);
        if (empty($return)) {
            return NULL;
        }

        return $return[0];
    }

    /**
     * Metoda pobierająca pojedyńczą wartość. Do przekazanego zapytania dodaje fragment " LIMIT 1".
     *
     * @public
     * @param string $_sql zapytanie SQL do wykonania
     * @param array [opt = array ( )] $_value parametry dla zapytania SQL
     * @return string|NULL pojedyńcza wartość pobranego rekordu lub NULL w przypadku braku pasującego rekordu
     */
    public function selectOneField($_sql, $_params = array())
    {
        $return = self::select("$_sql LIMIT 1", $_params);

        return (isset($return[0]) && count($return[0]) ? array_pop($return[0]) : NULL);
    }

    /**
     * Metoda aktywuje lub deaktywuje debugowanie zapytan.
     *
     * @public
     * @param string $_type rodzaj zapytan, dla ktorego ustalamy debugowanie, musi byc zdefiniowany w $debug
     * @param bool $_on czy debugowanie ma byc aktywne
     */
    public function setDebug($_type, $_on)
    {
        if (!isset($this->debug[$_type])) {
            throw new \InvalidArgumentException();
        }

        $this->debug[$_type] = (bool) $_on;
    }

    /**
     * Metoda wyświetlająca dumpa zapytania.
     * By zapytanie zostało wyświetlone musi być włączony tryb debugowania dla danego typu zapytań.
     *
     * @public
     * @param string $_type rodzaj zapytan, dla ktorego ustalamy debugowanie, musi byc zdefiniowany w $debug
     * @param string $_sql zapytanie SQL
     * @param array [opt = NULL] $_value parametry dla zapytania SQL
     */
    public function showDebug($_type, $_sql = NULL, $_params = NULL)
    {
        if (!isset($this->debug[$_type])) {
            throw new \InvalidArgumentException();
        }

        if (!$this->debug[$_type] && !$this->debug["all"]) {
            return;
        }

        $sqlFull = (($_sql && $_params)
            ? str_replace (array_keys($_params), array_map(function ($_) {return "'$_'";}, array_values($_params)), $_sql)
            : NULL
        );

        \Ker\Utils\Debug::dmp(array("trace" => false, "memory" => false,), "SQL DEBUG TYPE: " . strtoupper($_type), $_sql, $_params, $sqlFull);
    }

    /**
     * Metoda rozpoczynająca transakcję. Uwzglednia transakcje zagnieżdzone.
     *
     * @public
     */
    public function transactionBegin()
    {

        if (!$this->transactionNestable || $this->transactionLevel === 0) {
            $this->showDebug("transaction", "BEGIN");
            $this->instance->beginTransaction();
        } else {
            $this->showDebug("transaction", "SAVEPOINT LEVEL{$this->transactionLevel}");
            $this->instance->exec("SAVEPOINT LEVEL{$this->transactionLevel}");
        }
        ++$this->transactionLevel;
    }

    /**
     * Metoda zatwierdzająca transakcję. Uwzglednia transakcje zagnieżdzone.
     *
     * @public
     */
    public function transactionCommit()
    {
        --$this->transactionLevel;

        if (!$this->transactionNestable || $this->transactionLevel === 0) {
            $this->showDebug("transaction", "COMMIT");
            $this->instance->commit();

            return;
        }

        $this->showDebug("transaction", "RELEASE SAVEPOINT LEVEL{$this->transactionLevel}");
        $this->instance->exec("RELEASE SAVEPOINT LEVEL{$this->transactionLevel}");
    }

    /**
     * Metoda odrzucająca transakcję. Uwzglednia transakcje zagnieżdzone.
     *
     * @public
     */
    public function transactionRollBack()
    {
        --$this->transactionLevel;

        if (!$this->transactionNestable || $this->transactionLevel === 0) {
            $this->showDebug("transaction", "ROLLBACK");
            $this->instance->rollBack();

            return;
        }

        $this->showDebug("transaction", "ROLLBACK TO SAVEPOINT LEVEL{$this->transactionLevel}");
        $this->instance->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->transactionLevel}");
    }

    /**
     * Metoda dokonująca aktualizacji rekordów.
     *
     * @public
     * @param string $_sql zapytanie SQL do wykonania
     * @param array [opt = array ( )] $_value parametry dla zapytania SQL
     * @return int ilość zmodyfikowanych rekordów
     */
    public function update($_sql, $_params)
    {
        $this->showDebug("update", $_sql, $_params);

        $statement = $this->instance->prepare($_sql);
        $statement->execute($_params);
        $return = $statement->rowCount();
        $statement->closeCursor();

        return $return;
    }

    /**
     * Metoda dokonująca aktualizacji pojedyńczego rekordu. Do przekazanego zapytania dodaje fragment " LIMIT 1".
     *
     * @public
     * @param string $_sql zapytanie SQL do wykonania
     * @param array [opt = array ( )] $_value parametry dla zapytania SQL
     * @return int ilość zmodyfikowanych rekordów
     */
    public function updateOne($_sql, $_params)
    {
        return self::update("$_sql LIMIT 1", $_params);
    }

}
