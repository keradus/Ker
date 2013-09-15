<?php

namespace Ker;

/**
 * Klasa służąca do przechowywania ustawień konfiguracyjnych.
 * Korzysta z wzorca projektowego Property w wersji statycznej.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @date 2013-09-15 19:54:25
 */
class Config extends APropertyStatic
{

    /**
     * Kontener na dane.
     *
     * @static
     * @protected
     */
    protected static $container = array();

}
