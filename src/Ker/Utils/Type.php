<?php

namespace Ker\Utils;

/**
 * Klasa ze statycznymi metodami użytkowymi służącymi do operacji na typach.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:35:40
 */
class Type
{

    /**
     * Funkcja sprawdza czy przekazana tablica jest asocjacyjna
     *
     * @param array tablica do przetestowania
     * @return bool informacja czy tablica jest asocjacyjna
     */
    public static function arrayIsAssociative(array $_arr)
    {
        foreach (array_keys($_arr) as $key) {
            if (!is_int($key)) {
                return true;
            }
        }

        return false;
    }

}
