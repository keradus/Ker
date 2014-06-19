<?php

namespace Ker;

/**
 * Cecha implementująca wzorzec projektowy Property.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @date 2013-08-19 22:56:44
 * @abstract
 * @warning Wszelkie zmiany implementować również w bliźniaczej czesze StaticPropertyTrait.
 */
trait PropertyTrait
{

    /**
     * Kontener na dane.
     *
     * @protected
     */
    protected $container = [];

    /**
     * Metoda pobierająca elementy.
     *
     * @public
     * @param array|list|string $... elementy do pobrania: pojedyńcza nazwa, lista lub tablica nazw
     * @return mixed|array element lub tablica elementów
     */
    public function get()
    {
        $argsCount = func_num_args();

        if (!$argsCount) {
            throw new \BadMethodCallException("Parameter missing");
        }

        $names = (($argsCount > 1) ? func_get_args() : func_get_arg(0));

        if (!is_array($names)) {
            return $this->getOne($names);
        }

        $return = [];
        foreach ($names AS $name) {
            $return[$name] = $this->getOne($name);
        }

        return $return;
    }

    /**
     * Metoda pobierająca pojedyńczy element.
     *
     * @public
     * @param string $_name nazwa elementu do pobrania
     * @param mixed $_value wartość zwracana w przypadku braku elementu w zasobie, domyślnie [NULL]
     * @return mixed element
     */
    public function getOne($_name, $_value = NULL)
    {
        return $this->hasOne($_name) ? $this->container[$_name] : $_value;
    }

    /**
     * Metoda sprawdzająca obecność element w kontenerze.
     *
     * @public
     * @param string $_name nazwa elementu do pobrania
     * @return bool informacja o obecności elementu
     */
    public function hasOne($_name)
    {
        return array_key_exists($_name, $this->container);
    }

    /**
     * Metoda usuwająca elementy.
     *
     * @public
     * @param array|list|string $... elementy do usunięcia: pojedyńcza nazwa, lista lub tablica nazw
     * @exception \BadMethodCallException - wyjątek wyrzucany w sytuacji, gdy nie przekazano parametrów do funkcji
     */
    public function remove()
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
            $this->removeOne($name);
        }
    }

    /**
     * Metoda usuwająca wszystkie elementy.
     *
     * @public
     */
    public function removeAll()
    {
        $this->container = [];
    }

    /**
     * Metoda usuwająca element.
     *
     * @public
     * @param string $_name nazwa elementu do usunięcia
     */
    public function removeOne($_name)
    {
        unset($this->container[$_name]);
    }

    /**
     * Metoda zapisująca elementy.
     *
     * @public
     * @param string|array $_a nazwa do zapisania lub tablica o elementach [nazwa=>wartość] jeśli nie podano drugiego parametru funkcji lub zawierająca nazwy elementów
     * @param mixed|array $_b jeśli $_a to string - wartosc do zapisania, jeśli $_ to array - tablica wartości lub brak parametru (wtedy $_a zawiera i nazwy, i wartości)
     * @exception BadMethodCallException - wyjątek wyrzucany w sytuacji, gdy do funkcji przekazano niewłaściwe parametry
     */
    public function set()
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
            $this->setOne($key, $val);
        }
    }

    /**
     * Metoda zapisująca element.
     *
     * @public
     * @param string $_name nazwa elementu do zapisania
     * @param mixed $_value wartość elementu
     */
    public function setOne($_name, $_value)
    {
        $this->container[$_name] = $_value;
    }

    /**
     * Metoda konwertujaca obiekt do tablicy, zwracając wszystkie elementy kontenera.
     *
     * @public
     * @return array tablica elementów
     */
    public function toArray()
    {
        return $this->get(array_keys($this->container));
    }
}
