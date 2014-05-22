<?php

namespace Ker\CRUD;

/**
 * Interfejs reprezentujący komponent CRUD. Zapewnia on jednolite zarządzanie składowanymi rekordami.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @date 2013-09-15 20:04:31
 * @interface
 */
interface ICRUD
{

    /**
     * Metoda usuwająca rekord.
     *
     * @static
     * @public
     */
    public static function destroy($_ = NULL);

    /**
     * Konstruktor.
     *
     * @public
     * @return CRUD instancja obiektu
     */
    public function __construct($_ = NULL);

    /**
     * Metoda usuwająca rekord.
     *
     * @public
     */
    public function delete($_ = NULL);

    /**
     * Metoda zapisująca rekord.
     *
     * @public
     * @return mixed identyfikator zapisanego obiektu
     */
    public function save($_ = NULL);
}
