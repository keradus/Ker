<?php

namespace Ker;

/**
 * Description of FormType
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:56:44
 * @todo TASK: #57
 */
class FormType extends Enum
{

    const html = 0;
    const text = 1;
    const password = 2;
    const hidden = 3;
    const textarea = 4;
    const select = 5;
    const radio = 6;
    const checkbox = 7;
    const file = 8;

    public static $strict = true;
    public static $defaultTextareaSize = array(
        "rows" => 5,
        "cols" => 50,
    );
    private static $prototype;

    public static function __callStatic($_name, $_arguments = null)
    {
        return parent::__callStatic($_name, $_arguments);
    }

    public static function __constructStatic()
    {
        static $isInitialized = false;

        if ($isInitialized) {
            return;
        }

        $isInitialized = true;

        $basePrototype = array(
            "name" => "",
            "label" => "",
            "value" => NULL,
            "default" => NULL,
            "errors" => array(),
            "itemId" => "",
            "itemClass" => "",
            "itemStyle" => "",
            "labelId" => "",
            "labelClass" => "",
            "labelStyle" => "",
            "allowHtml" => false,
            "check" => array(),
            "readonly" => "",
            "extraData" => array(),
        );

        static::$prototype = array(
            self::html => array(
                "value" => "",
            ),
            self::text => array_merge($basePrototype, array("value" => "", "default" => "",)),
            self::password => array_merge($basePrototype, array("value" => "", "default" => "",)),
            self::hidden => array_merge($basePrototype, array("value" => "", "default" => "",)),
            self::textarea => array_merge($basePrototype, array("value" => "", "default" => "",), static::$defaultTextareaSize),
            self::select => array_merge($basePrototype, array("values" => "",)),
            self::radio => array_merge($basePrototype, array("values" => "",)),
            self::checkbox => array_merge($basePrototype, array("values" => "",)),
            self::file => $basePrototype,
        );
    }

    public static function computeItem(FormType $_type, $_item)
    {
        $item = static::$prototype[$_type->__toString()];
        $countPrototype = count($item);
        $item = array_merge($item, $_item);

        // jesli rozszerzono prototyp - zabron
        if (count($item) !== $countPrototype) {
            throw new \InvalidArgumentException("Too many fields!");
        }

        // jesli nie podano nazwy - zabron
        if (array_key_exists("name", $item) && $item["name"] === "") {
            throw new \InvalidArgumentException("Empty name is forbidden!");
        }

        //W values przy sprawdzaniu czy wybrano jedna z opcji sprawdzamy przez == a nie === - mozemy ustawic wartosc jako int'a, a w formularzu przekazuje sie string.
        //By nie bylo problemu na pustym stringu (0 == "") zabraniam podawac wartosci "" jako wartosc przy select/radio/checkbox.
        //Byc moze w przyszlosci zostanie to poprawione i bedziemy rozrozniac "0" !== "" - wymaga to konwersji kluczy do int'a. Do rozwazenia i przemyslenia. Przy PHP 5.2 byly przy takim podejsciu jakies problemy, nie pamietam niestety jakie :(
        if (array_key_exists("values", $item) && is_array($item["values"]) && array_key_exists("", $item["values"])) {
            throw new \InvalidArgumentException("Empty value for select/radio/checkbox is forbidden!");
        }

        return $item;
    }

    public function __toString()
    {
        return parent::__toString();
    }

    public function isStringType()
    {
        return $this->inArray(array(self::text, self::password, self::textarea));
    }

    public function isTextType()
    {
        return $this->inArray(array(self::text, self::textarea));
    }

}

FormType::__constructStatic();
