<?php

namespace Ker;

/**
 * Klasa służąca do zarządzania sesją użytkownika.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:56:44
 */
class Session
{

    /**
     * Ilość sekund, co jaką wykonywana jest regeneracja sesji
     *
     * @static
     * @private
     */
    private static $regenerateSec;

    /**
     * Metoda tworząca sesję - ustawia opcje sesji oraz w razie potrzeby tworzy sesję na serwerze.
     *
     * @static
     * @public
     * @param string $_name nazwa sesji
     * @param int    $_time dlugość życia sesji w minutach
     */
    public static function create($_name = "CMT", $_time = 30, $_path = "./temp_session")
    {
        static::$regenerateSec = 60 * $_time;
        session_save_path($_path);
        session_name($_name);
        session_start();

        if (!isset($_SESSION["safe"]["started"])) {
            static::startNewSession();
        } else {
            if (isset($_SESSION["safe"]["time"]) && ((int) $_SESSION["safe"]["time"] + static::$regenerateSec < time())) {
                static::startNewSession();
            }
            $_SESSION["safe"]["time"] = time();

            if (
                    isset($_SESSION["browser"]) && isset($_SESSION["ip"]) && ($_SESSION["browser"] != $_SERVER["HTTP_USER_AGENT"] || $_SESSION["ip"] != $_SERVER["REMOTE_ADDR"])
            ) {
                static::startNewSession();
            }
        }
    }

    /**
     * Metoda pobierająca login użytkownika.
     *
     * @static
     * @public
     * @return string login użytkownika
     */
    public static function getLogin()
    {
        return ((isset($_SESSION["safe"]) && isset($_SESSION["safe"]["login"])) ? $_SESSION["safe"]["login"] : null);
    }

    /**
     * Metoda pobierająca id użytkownika.
     *
     * @static
     * @public
     * @return int id użytkownika
     */
    public static function getIdUser()
    {
        return ((isset($_SESSION["safe"]) && isset($_SESSION["safe"]["idUser"])) ? $_SESSION["safe"]["idUser"] : null);
    }

    /**
     * Metoda pobierająca stopień uprawnień użytkownika.
     *
     * @static
     * @public
     * @return int stopień uprawnień użytkownika
     */
    public static function getPerm()
    {
        return ((isset($_SESSION["safe"]) && isset($_SESSION["safe"]["perm"])) ? $_SESSION["safe"]["perm"] : null);
    }

    /**
     * Metoda zapisująca informacje o zalogowanym użytkowniku.
     *
     * @static
     * @public
     * @param int    $_idUser    id użytkownika
     * @param string $_login     login użytkownika
     * @param int    $_permLevel stopień uprawnień użytkownika
     */
    public static function logIn($_idUser, $_login, $_permLevel)
    {
        $_SESSION["safe"]["login"] = $_login;
        $_SESSION["safe"]["idUser"] = $_idUser;
        $_SESSION["safe"]["perm"] = $_permLevel;
    }

    /**
     * Metoda usuwająca informacje o zalogowanym użytkowniku. Procz wyczyszczenia danych użytkownika następuje całkowite wyczyszczenie sesji i stworzenie nowej.
     *
     * @static
     * @public
     */
    public static function logOut()
    {
        $_SESSION["safe"]["login"] = "";
        $_SESSION["safe"]["idUser"] = "";
        $_SESSION["safe"]["perm"] = "";
        session_destroy();
        session_start();
        static::startNewSession();
    }

    /**
     * Metoda restartująca sesję. Zmienia klucz sesji, niszczy dane starej sesji (również fizycznie) zaś w nowej wprowadza wartości domyślne (np. IP).
     *
     * @static
     * @public
     */
    private static function startNewSession()
    {
        session_regenerate_id(true);
        $_SESSION = array(); //dla pewnosci, wg niektorych zrodel session_regenerate_id zachowuje dane !
        //jednoczesnie na jednym serwerze (XAMPP) zauwazylem, iz bez zamkniecia i na nowo otwarcia sesji skrypt pozostaje przy starej sesji
        //
        // zapamietanie id sesji
        $sid = session_id();

        // zamkniecie sesji
        session_write_close();

        //  otworzenie sesji o znanym id
        session_id($sid);
        session_start();

        $_SESSION["safe"]["started"] = true; //anty Session Fixation
        $_SESSION["safe"]["time"] = time();
        $_SESSION["safe"]["ip"] = $_SERVER["REMOTE_ADDR"]; //anty Session Hijacking
        $_SESSION["safe"]["browser"] = $_SERVER["HTTP_USER_AGENT"]; //anty Session Hijacking
        $_SESSION["safe"]["login"] = "";
        $_SESSION["safe"]["idUser"] = "";
        $_SESSION["safe"]["perm"] = "";

        return;
    }

}
