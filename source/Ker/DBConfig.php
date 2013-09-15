<?php

namespace Ker;

/**
 * Klasa służąca do przechowywania ustawień konfiguracyjnych połączenia z baza danych.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @date 2013-09-15 21:26:43
 */
class DBConfig
{

    /**
     * Data Source Name, czyli nazwa źródła danych.
     *
     * @public
     */
    public $dsn;

    /**
     * Nazwa hosta.
     *
     * @public
     */
    public $host;

    /**
     * Numer portu.
     *
     * @public
     */
    public $port;

    /**
     * Nazwa bazy danych.
     *
     * @public
     */
    public $database;

    /**
     * Nazwa użytkownika.
     *
     * @public
     */
    public $user;

    /**
     * Hasło użytkownika w formie jawnej.
     *
     * @public
     */
    public $password;

    /**
     * Konstruktor klasy.
     *
     * @public
     * @param string $_dsn Data Source Name, czyli nazwa źródła danych
     * @param string $_host nazwa hosta
     * @param int $_port numer portu
     * @param string $_database nazwa bazy danych
     * @param string $_user nazwa użytkownika
     * @param string $_password hasło użytkownika w formie jawnej
     * @return DBConfig instancja obiektu
     */
    public function __construct($_dsn, $_host, $_port, $_database, $_user, $_password)
    {
        $this->dsn = $_dsn;
        $this->host = $_host;
        $this->port = $_port;
        $this->database = $_database;
        $this->user = $_user;
        $this->password = $_password;
    }

}
