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
 * @date 2013-09-15 20:06:09
 * @abstract
 */
abstract class ADB extends \Ker\AProperty implements ICRUD
{

}
