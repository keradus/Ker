<?php

namespace Ker;

/**
 * Klasa służąca do komunikacji z baza danych. Implementuje wzorzec projektowy adapter dla klasy PDO.
 * Zapewnia uproszczony interfejs dla najczęstszych akcji.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @date 2013-09-15 21:26:43
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

}
