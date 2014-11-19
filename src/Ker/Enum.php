<?php

namespace Ker;

/**
 * Klasa implementująca prototyp klas wyliczeniowych, wzorowana na SplEnum, jednak nie posiada __default, zaś do porównania należy użyć equals() a nie ==/=== (brak możliwości przeciążenia operatorów).
 *
 * @abstract
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:56:44
 */
abstract class Enum
{

    /**
     * Zmienna ustalająca czy domyślnie porównywane mają być również typy.
     *
     * @static
     * @public
     */
    public static $strict = true;

    /**
     * Zmienna przechowująca wartość enum'a.
     *
     * @protected
     */
    protected $value;

    /**
     * Konstruktor klasy, ustala wartość enum'a.
     *
     * @access public
     * @param  mixed $_value ustalana wartość
     * @return Enum  instancja klasy
     * @exception UnexpectedValueException - wyjątek rzucany w sytuacji, gdy wartość z parametru nie znajduje się w enumie
     */
    public function __construct($_value)
    {
        if (!in_array($_value, self::getConstList(), true)) {
            throw new \UnexpectedValueException();
        }

        $this->value = $_value;
    }

    /**
     * Magiczna metoda tworząca nową instancję klasy, wywoływana przez odwołanie się do stałych klasy jak gdyby były metodami.
     *
     * @static
     * @public
     * @param  string             $_value     ustalana wartość
     * @param  mixed [opt = NULL] $_arguments opcjonalne parametry (dla spójności z klasami pochodnymi)
     * @return Enum               instancja klasy
     * @exception UnexpectedValueException - wyjątek rzucany w sytuacji, gdy wartość z parametru nie znajduje się w enumie
     * @warning W klasie potomnej należy ponownie zdefiniować tą metodę (metody magiczne nie są dziedziczone), np. wywołując w niej metode bazową
     */
    public static function __callStatic($_name, $_arguments = null)
    {
        return ( new static(self::getConstant($_name)) );
    }

    /**
     * Metoda pobierająca wartośc wybranej składowej enuma.
     *
     * @static
     * @public
     * @param  string $_name nazwa wybranej składowej enuma
     * @return array  dopuszczalne wartości
     */
    public static function getConstant($_name)
    {
        $r = new \ReflectionClass(get_called_class());

        return $r->getConstant($_name);
    }

    /**
     * Metoda pobierająca dopuszczalne wartości enum'a.
     *
     * @static
     * @public
     * @return array dopuszczalne wartości
     */
    public static function getConstList()
    {
        $r = new \ReflectionClass(get_called_class());

        return $r->getConstants();
    }

    /**
     * Magiczna metoda konwertująca obiekt do napisu.
     *
     * @public
     * @return string napis reprezentujący wartość obiekt
     */
    public function __toString()
    {
        return (string) $this->value;
    }

    /**
     * Metoda sprawdzająca, czy przekazana wartość jest równa wartości obiektu.
     *
     * @public
     * @param  mixed $_value  sprawdzana wartość
     * @param  bool  $_strict [opt = NULL] flaga ustalajaca, czy porownanie ma sprawdzać również typ, jeśli flaga jest pusta brane jest domyślne ustawienie
     * @return bool  informacja o równości
     */
    public function equals($_value, $_strict = null)
    {
        $strict = ($_strict ? $_strict : static::$strict);
        if ($strict) {
            return ($this->value === $_value);
        }

        return ($this->value == $_value);
    }

    /**
     * Metoda sprawdzająca, czy obecna wartość enuma występuje w przekazanej tablicy.
     *
     * @public
     * @param  array $_values tablica wartości, wśród których poszukujemy obecnej wartości enuma.
     * @param  bool  $_strict [opt = NULL] flaga ustalajaca, czy porownanie ma sprawdzać również typ, jeśli flaga jest pusta brane jest domyślne ustawienie
     * @return bool  informacja o zawieraniu sie obecnej wartości w przekazanej tablicy
     */
    public function inArray($_values, $_strict = null)
    {
        $strict = ($_strict ? $_strict : static::$strict);

        return in_array($this->value, $_values, $strict);
    }

}
