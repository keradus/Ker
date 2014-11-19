<?php

namespace Ker;

/**
 * Klasa wyliczeniowa definiująca typy wiadomości dla klasy Messages.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:56:44
 */
class MessageType extends Enum
{

    /**
     * Zmienna ustalająca czy domyślnie porównywane mają być również typy.
     *
     * @static
     * @public
     */
    public static $strict = true;

    /**
     * Stała definiująca pozytywny typ komunikatu.
     *
     * @public
     */

    const ok = 0;
    /**
     * Stała definiująca neutralny typ komunikatu.
     *
     * @public
     */
    const info = 1;
    /**
     * Stała definiująca negatywny typ komunikatu.
     *
     * @public
     */
    const error = 2;

    /**
     * Magiczna metoda tworząca nową instancję klasy, wywoływana przez odwołanie się do stałych klasy jak gdyby były metodami.
     *
     * @static
     * @public
     * @param  string $_value     ustalana wartość
     * @param  mixed  $_arguments [opt = NULL] opcjonalne parametry (dla spójności z klasami pochodnymi)
     * @return Enum   instancja klasy
     * @exception UnexpectedValueException - wyjątek rzucany w sytuacji, gdy wartość z parametru nie znajduje się w enumie
     * @warning W klasie potomnej należy ponownie zdefiniować tą metodę (metody magiczne nie są dziedziczone), np. wywołując w niej metode bazową
     */
    public static function __callStatic($_name, $_arguments = null)
    {
        return parent::__callStatic($_name, $_arguments);
    }

    /**
     * Magiczna metoda konwertująca obiekt do napisu.
     *
     * @public
     * @return string napis reprezentujący wartość obiekt
     */
    public function __toString()
    {
        return parent::__toString();
    }

}
