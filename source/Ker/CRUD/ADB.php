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

}
