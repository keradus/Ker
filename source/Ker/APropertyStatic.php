<?php

namespace Ker;

/**
 * Klasa implementująca wzorzec projektowy Property w wersji statycznej.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @date 2013-08-19 22:56:44
 * @abstract
 * @warning Wszelkie zmiany implementować również w bliźniaczej klasie AProperty.
 */
abstract class APropertyStatic
{

    /**
     * Kontener na dane.
     *
     * @static
     * @protected
     */
    protected static $container = array();

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
        $func_num_args = func_num_args();

        if (!$func_num_args) {
            throw new \Ker\Ex\NoParameter ();
        } elseif ($func_num_args === 1) {
            $name = func_get_arg(0);
            if (is_array($name)) {
                $return = array();
                foreach ($name AS $item) {
                    $return[$item] = static::getOne($item);
                }

                return $return;
            }

            return static::getOne($name);
        } else {
            $return = array();
            foreach (func_get_args() AS $name) {
                $return[$name] = static::getOne($name);
            }

            return $return;
        }
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
        return isset(static::$container[$_name]);
    }

    /**
     * Metoda usuwająca elementy.
     *
     * @static
     * @public
     * @param array|list|string $... elementy do usunięcia: pojedyńcza nazwa, lista lub tablica nazw
     * @exception \Ker\Ex\NoParameter - wyjątek wyrzucany w sytuacji, gdy nie przekazano parametrów do funkcji
     */
    public static function remove()
    {
        $func_num_args = func_num_args();

        if (!$func_num_args) {
            throw new \Ker\Ex\NoParameter ();
        } elseif ($func_num_args === 1) {
            $name = func_get_arg(0);
            if (is_array($name)) {
                foreach ($name AS $item) {
                    static::removeOne($item);
                }
            } else {
                static::removeOne($name);
            }
        } else {
            foreach (func_get_args() AS $name) {
                static::removeOne($name);
            }
        }
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
        $func_num_args = func_num_args();

        if (!$func_num_args) {
            throw new \Ker\Ex\NoParameter ();
        } elseif ($func_num_args === 1) {
            $arr = func_get_arg(0);
            if (!is_array($arr)) {
                throw new \BadMethodCallException("Only one parameter, but it is not array");
            }
            foreach ($arr AS $key => $val) {
                static::setOne($key, $val);
            }
        } elseif ($func_num_args === 2) {
            $first = func_get_arg(0);
            $second = func_get_arg(1);
            if (is_array($first)) {
                if (!is_array($second)) {
                    throw new \BadMethodCallException("The first parameter is array, but the second does not");
                }
                if (count($first) !== count($second)) {
                    throw new \BadMethodCallException("Passed arrays are of different sizes");
                }
                $arr = array_combine($first, $second);
                foreach ($arr AS $key => $val) {
                    static::setOne($key, $val);
                }
            } else {
                static::setOne($first, $second);
            }
        } else {
            throw new \BadMethodCallException("Too many arguments");
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

}
