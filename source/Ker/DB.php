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

}
