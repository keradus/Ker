<?php

namespace Ker;

/**
 * Klasa obsługująca manipulacje datą.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:56:44
 * @extends DateTime
 */
class DateFull extends \DateTime
{

    /**
     * Magiczna metoda rzutowania obiektu do napisu.
     *
     * @public
     * @return string Data i czas w formacie YYYY-MM-DD GG:MM:SS
     */
    public function __toString()
    {
        return $this->format("Y-m-d H:i:s");
    }

}
