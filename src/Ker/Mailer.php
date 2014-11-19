<?php

namespace Ker;

/**
 * Klasa obsługująca wysyłkę wiadomości mailowych.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:56:44
 */
class Mailer
{

    /**
     * Tag dodawany na początek tytułu maila.
     *
     * @static
     * @public
     */
    public static $tag = "";

    /**
     * Domyślne nagłówki maila
     *
     * @static
     * @public
     */
    public static $headers = array(
        "mime" => "MIME-Version: 1.0",
        "content" => "Content-type: text/html; charset=UTF-8",
        "from" => "From: from <mail>",
    );

    /**
     * Metoda przygotowuje napis zawierający nagłówki mailowe na podstawie otrzymanych nagłówków uzupełniając je o nagłówki domyślne.
     *
     * @static
     * @private
     * @param  array  $_headers [opt = array ( )] nagłówki dodawane do wiadomości
     * @return string wynikowy napis z nagłówkami
     */
    private static function headersPrepare($_headers = array())
    {
        $currentHeaders = array_merge(static::$headers, $_headers);
        $return = "";
        foreach ($currentHeaders as $header) {
            $return .= $header . "\r\n";
        }

        return $return;
    }

    /**
     * Metoda wysyłająca maila.
     *
     * @static
     * @public
     * @param string $_to      adresat
     * @param string $_title   tytuł
     * @param string $_msg     wiadomość
     * @param array  $_headers [opt = array ( )] nagłówki dodawane do wiadomości
     */
    public static function send($_to, $_title, $_msg, $_headers = array())
    {
        $headers = static::headersPrepare($_headers);
        //echo "$_to/$_title/$_msg";
        mail($_to, static::$tag . $_title, $_msg, $headers);
    }

    /**
     * Metoda wysyłająca maila z udawaniem wysłania go od określonego adresata.
     *
     * @static
     * @public
     * @param string $_to       adresat
     * @param string $_title    tytuł
     * @param string $_msg      wiadomość
     * @param string $_fromName nazwa nadawcy
     * @param string $_formMail mail nadawcy
     * @param array  $_headers  [opt = array ( )] nagłówki dodawane do wiadomości
     */
    public static function sendFromSb($_to, $_title, $_msg, $_fromName, $_fromMail, $_headers = array())
    {
        $_headers["from"] = "From: $_fromName <$_fromMail>";

        return static::send($_to, $_title, $_msg, $_headers);
    }

}
