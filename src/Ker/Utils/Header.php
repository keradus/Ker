<?php

namespace Ker\Utils;

/**
 * Klasa ze statycznymi metodami zarządzania nagłówkami.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:35:01
 */
class Header
{

    /**
     * Funkcja przekierowująca na zadany adres za pomocą wysłania headerów.
     *
     * @param string $_location  lokalizacja, na którą mamy zostać przeniesieni
     * @param string $_preHeader [opt = NULL] nagłówek wysyłany przed nagłókiem zmieniającym lokalizację, np. HTTP/1.1 301 Moved Permanently
     * @warning przed uruchomieniem tej funkcji NIE może zostać wysłany żaden inny nagłówek do przeglądarki!
     */
    public static function redirect($_location, $_preHeader = null)
    {
        if ($_preHeader) {
            header($_preHeader);
        }
        header("Location: $_location");
        header("Connection: close");
        exit();
    }

    /**
     * Funkcja sprawdza, czy strona została uruchomiona z www na początku adresu - jeżeli nie przekierowuje na adres z www.
     *
     */
    public static function redirectIfNotWww()
    {
        $HTTP_HOST = explode(".", $_SERVER["HTTP_HOST"]);
        if ($HTTP_HOST[0] !== "www") {
            static::redirect("http://www." . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], "HTTP/1.1 301 Moved Permanently");
        }
    }

    // TASK: #150 - dokumentacja
    public static function redirectPermanently($_location)
    {
        static::redirect($_location, "HTTP/1.1 301 Moved Permanently");
    }

    // TASK: #150 - dokumentacja
    public static function temporaryUnavailable($_ = array())
    {
        header("HTTP/1.1 503 Service Temporarily Unavailable");
        header("Status: 503 Service Temporarily Unavailable");

        if (isset($_["retry"]) && $_["retry"]) {
            header("Retry-After: 60");
        }

        header("Connection: close");
        exit();
    }

    // TASK: #150 - dokumentacja
    public static function forceNoCache()
    {
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: pre-check=0, post-check=0, max-age=0");
        header("Pragma: no-cache");
    }

    // TASK: #150 - dokumentacja
    public static function download($_)
    {
        header("Content-Disposition: attachment; filename=" . urlencode($_["filename"]));
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Description: File Transfer");
        header("Content-Length: " . filesize($_["filepath"]));
        echo file_get_contents($_["filepath"]);
        exit();
    }

}
