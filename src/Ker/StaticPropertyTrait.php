<?php

namespace Ker;

/**
 * Cecha implementująca wzorzec projektowy Property w wersji statycznej.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @date 2013-08-19 22:56:44
 * @abstract
 * @warning Wszelkie zmiany implementować również w bliźniaczej czesze PropertyTrait.
 */
trait StaticPropertyTrait
{

    /**
     * Kontener na dane.
     *
     * @static
     * @protected
     */
    protected static $container = [];

    /**
     * Metoda pobierająca elementy.
     *
     * @static
     * @public
     * @param array|list|string $... elementy do pobrania: pojedyńcza nazwa, lista lub tablica nazw
     * @return mixed|array element lub tablica elementów
     */
    public static function get()
    {
        $argsCount = func_num_args();

        if (!$argsCount) {
            throw new \BadMethodCallException("Parameter missing");
        }

        $names = (($argsCount > 1) ? func_get_args() : func_get_arg(0));

        if (!is_array($names)) {
            return static::getOne($names);
        }

        $return = [];
        foreach ($names AS $name) {
            $return[$name] = static::getOne($name);
        }

        return $return;
    }

    /**
     * Metoda pobierająca pojedyńczy element.
     *
     * @static
     * @public
     * @param string $_name nazwa elementu do pobrania
     * @param mixed $_value wartość zwracana w przypadku braku elementu w zasobie, domyślnie [NULL]
     * @return mixed element
     */
    public static function getOne($_name, $_value = NULL)
    {
        return static::hasOne($_name) ? static::$container[$_name] : $_value;
    }

    /**
     * Metoda sprawdzająca obecność element w kontenerze.
     *
     * @static
     * @public
     * @param string $_name nazwa elementu do pobrania
     * @return bool informacja o obecności elementu
     */
    public static function hasOne($_name)
    {
        return array_key_exists($_name, static::$container);
    }

    /**
     * Metoda usuwająca elementy.
     *
     * @static
     * @public
     * @param array|list|string $... elementy do usunięcia: pojedyńcza nazwa, lista lub tablica nazw
     * @exception \BadMethodCallException - wyjątek wyrzucany w sytuacji, gdy nie przekazano parametrów do funkcji
     */
    public static function remove()
    {
        $argsCount = func_num_args();

        if (!$argsCount) {
            throw new \BadMethodCallException("Parameter missing");
        }

        $names = (($argsCount > 1) ? func_get_args() : func_get_arg(0));

        if (!is_array($names)) {
            $names = [$names, ];
        }

        foreach ($names AS $name) {
            static::removeOne($name);
        }
    }

    /**
     * Metoda usuwająca wszystkie elementy.
     *
     * @static
     * @public
     */
    public static function removeAll()
    {
        static::$container = [];
    }

    /**
     * Metoda usuwająca element.
     *
     * @static
     * @public
     * @param string $_name nazwa elementu do usunięcia
     */
    public static function removeOne($_name)
    {
        unset(static::$container[$_name]);
    }

    /**
     * Metoda zapisująca elementy.
     *
     * @static
     * @public
     * @param string|array $_a nazwa do zapisania lub tablica o elementach [nazwa=>wartość] jeśli nie podano drugiego parametru funkcji lub zawierająca nazwy elementów
     * @param mixed|array $_b jeśli $_a to string - wartosc do zapisania, jeśli $_ to array - tablica wartości lub brak parametru (wtedy $_a zawiera i nazwy, i wartości)
     * @exception BadMethodCallException - wyjątek wyrzucany w sytuacji, gdy do funkcji przekazano niewłaściwe parametry
     */
    public static function set()
    {
        $argsCount = func_num_args();

        if (!$argsCount) {
            throw new \BadMethodCallException("Parameter missing");
        }

        if ($argsCount > 2) {
            throw new \BadMethodCallException("Too many arguments");
        }

        $dictionary = [];

        if ($argsCount === 1) {
            $dictionary = func_get_arg(0);

            if (!is_array($dictionary)) {
                throw new \BadMethodCallException("Only one parameter, but it is not array");
            }
        } elseif ($argsCount === 2) {
            $keys = func_get_arg(0);
            $args = func_get_arg(1);

            if (is_array($keys)) {
                if (!is_array($args)) {
                    throw new \BadMethodCallException("The first parameter is array, but the second does not");
                }

                if (count($keys) !== count($args)) {
                    throw new \BadMethodCallException("Passed arrays are of different sizes");
                }

                $dictionary = array_combine($keys, $args);
            } else {
                $dictionary[$keys] = $args;
            }
        }

        foreach ($dictionary AS $key => $val) {
            static::setOne($key, $val);
        }
    }

    /**
     * Metoda zapisująca element.
     *
     * @static
     * @public
     * @param string $_name nazwa elementu do zapisania
     * @param mixed $_value wartość elementu
     */
    public static function setOne($_name, $_value)
    {
        static::$container[$_name] = $_value;
    }

    /**
     * Metoda konwertujaca obiekt do tablicy, zwracając wszystkie elementy kontenera.
     *
     * @static
     * @public
     * @return array tablica elementów
     */
    public static function toArray()
    {
        return static::get(array_keys(static::$container));
    }
}
