<?php

namespace Ker\Utils;

/**
 * Klasa ze statycznymi metodami użytkowymi służącymi do operacji na kodowaniu.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:35:16
 */
class Coding
{

    public static $iso2utf = array(
        "\xb1" => "\xc4\x85", "\xa1" => "\xc4\x84",
        "\xe6" => "\xc4\x87", "\xc6" => "\xc4\x86",
        "\xea" => "\xc4\x99", "\xca" => "\xc4\x98",
        "\xb3" => "\xc5\x82", "\xa3" => "\xc5\x81",
        "\xf3" => "\xc3\xb3", "\xd3" => "\xc3\x93",
        "\xb6" => "\xc5\x9b", "\xa6" => "\xc5\x9a",
        "\xbc" => "\xc5\xba", "\xac" => "\xc5\xb9",
        "\xbf" => "\xc5\xbc", "\xaf" => "\xc5\xbb",
        "\xf1" => "\xc5\x84", "\xd1" => "\xc5\x83"
    );

    public static function iso2utf($item)
    {
        if (is_array($item)) {
            foreach ($item as $key => $val) {
                $item[$key] = static::iso2utf($val);
            }

            return $item;
        }

        return strtr($item, static::$iso2utf);
    }

    public static function utf2iso($item)
    {
        if (is_array($item)) {
            foreach ($item as $key => $val) {
                $item[$key] = static::utf2iso($val);
            }

            return $item;
        }

        return strtr($item, array_flip(static::$iso2utf));
    }

}
