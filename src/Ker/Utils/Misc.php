<?php

namespace Ker\Utils;

/**
 * Klasa ze statycznymi metodami użytkowymi.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:35:01
 */
class Misc
{

    /**
     * Funkcja łącząca tablice i zwracająca jedynie wartości unikalne.
     *
     * @param  array $... lista tablic
     * @return array wynikowa tablica
     * @exception BadMethodCallException - wyjątek wyrzucany w sytuacji, gdy nie przekazaliśmy parametrów
     * @exception InvalidArgumentException - wyjątek wyrzucany w sytuacji, gdy którykolwiek z parametrów nie jest tablicą
     * @todo TASK: #12, #13
     */
    public static function & arrayMergeWithoutRedundancyValues(/* list of arrays */)
    {
        if (!func_num_args()) {
            throw new \BadMethodCallException("Parameter missing");
        }

        $ret = array();

        foreach (func_get_args() as $arg) {
            if ($arg === NULL) {
                continue;
            }

            if (!is_array($arg)) {
                throw new \InvalidArgumentException();
            }

            $ret = array_merge($ret, $arg);
        }

        $ret = array_unique($ret);

        return $ret;
    }

    //TODO: dokumentacja
    public static function arrayWeightedAverage(& $_ratings)
    {
        $total = 0;
        $count = 0;

        foreach ($_ratings as $number => $frequency) {
            $total += $number * $frequency;
            $count += $frequency;
        }

        if (!$count) {
            return NULL;
        }

        return $total / $count;
    }

    //TODO: dokumentacja
    public static function determineSubdomain($_)
    {
        if (!preg_match("/(.+)\.{$_["domain"]}$/", $_SERVER["HTTP_HOST"], $matches)) {
            return NULL;
        }

        $subdomain = $matches[1];

        if (isset($_["ignoreWww"]) && $_["ignoreWww"] && substr($subdomain, 0, 4) === "www.") {
            $subdomain = substr($subdomain, 4);
        }

        return $subdomain;
    }

    //TODO: dokumentacja
    public static function getExchangeRates(/* list */)
    {
        static $file = "http://www.nbp.pl/kursy/xml/LastA.xml";

        $args = func_get_args();

        $result = array();

        $xml = simplexml_load_file($file);
        foreach ($xml->pozycja as $pozycja) {
            if (!in_array($pozycja->kod_waluty, $args))
                continue;
            $currency = (string) $pozycja->kod_waluty;
            $result[$currency] = (string) round(str_replace(",", ".", (string) $pozycja->kurs_sredni), 2); //rzutowanie na stringa by przy serializacji bylo mniej do zapisania - o dziwo bez tego zapisuje np. 3.399999999999999911182158029987476766109466552734375 zamiast 3.4
        }

        return $result;
    }

    //TODO: dokumentacja
    public static function getGmailMessages($_login, $_token)
    {
        $feed = new \Ker\External\GmAtom($_login, $_token);

        $msgCount = $feed->check();
        if (false === $msgCount) {
            return NULL;
        }

        $feed->receiveAll();
        $messages = $feed->messages;

        foreach ($messages as & $item) {
            $date = new \Ker\DateFull($item->date);
            $item->date = $date->setTimezone(new \DateTimeZone("Europe/Warsaw"));
        }

        return array(
            "count" => $msgCount,
            "messages" => $messages,
        );
    }

    //TODO: dokumentacja
    public static function getMicrotime()
    {
        list($usec, $sec) = explode(" ", microtime());

        return ((float) $usec + (float) $sec);
    }

    /**
     * Funkcja sprawdza, czy kod błędu jest kodem naruszenia integralności z klasy PDO
     *
     * @param  string $_errorCode kod błędu
     * @return bool   czy kod błędu jest poprawny
     */
    public static function isPDOIntegrityError($_errorCode)
    {
        return in_array($_errorCode, array("HY000", "23000"));
    }

    /**
     * Funkcja kończy buforowanie output'a i uwalnia przechwyconą zawartość.
     *
     * @param bool $_obState flaga mówiąca o obecnym stanie buforowania (włączone/wyłączone)
     */
    public static function obFinish($_obState)
    {
        if ($_obState) {
            @ob_end_flush();
        }
    }

    /**
     * Funkcja uwalnia przechwyconą zawartość output'a bez zakończenia buforowania.
     *
     * @param bool $_obState flaga mówiąca o obecnym stanie buforowania (włączone/wyłączone)
     */
    public static function obFlush($_obState)
    {
        if ($_obState) {
            ob_flush();
        }
        flush();
    }

    /**
     * Funkcja rozpoczyna buforowanie output'a, sprawdzając czy może przy tym korzystać z kompresji.
     *
     * @return bool powodzenie rozpoczęcia buforowania
     */
    public static function obStart()
    {
        $obState = false;
        if (substr_count($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip")) {
            $obState = ob_start("ob_gzhandler");
        }
        if (!$obState) {
            $obState = ob_start();
        }

        return $obState;
    }

    //TODO: dokumentacja
    public static function registerErrorException()
    {
        set_error_handler(function ($_errno, $_errstr, $_errfile, $_errline) {
            throw new \ErrorException($_errstr, $_errno, 0, $_errfile, $_errline);
        });
    }

}
