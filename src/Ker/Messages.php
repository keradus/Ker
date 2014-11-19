<?php

namespace Ker;

/**
 * Klasa służąca jako kontener na komunikaty, które mogą zostać wygenerowane w dowolnym miejscu kodu. Dzieki niej możemy kolekcjonować komunikaty na całym przepływie aplikacji i dopiero w ustalonym momencie je obsłużyć.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:56:44
 */
class Messages
{

    /**
     * Zmienna będąca kontenerem na komunikaty.
     *
     * @access protected
     * @static
     * @var array
     */
    protected static $items = array();

    /**
     * Metoda dodająca pojedyńczy komunikat.
     *
     * @static
     * @public
     * @param MessageType $_type    typ komunikatu
     * @param string      $_message tresc komunikatu
     * @param string [opt = NULL] unikalne id komunikatu, jeśli istnieje już komunikat o podanym id zostanie on zastąpiony
     */
    public static function add(MessageType $_type, $_message, $_id = null)
    {
        $message = array("type" => $_type->__toString(), "message" => $_message);

        if ($_id) {
            static::$items[$_id] = $message;
        } else {
            static::$items[] = $message;
        }
    }

    /**
     * Metoda pobierająca wszystkie komunikaty.
     *
     * @static
     * @public
     * @return array komunikaty
     */
    public static function getAll()
    {
        return static::$items;
    }

}
