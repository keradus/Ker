<?php

namespace Ker\API;

/**
 * Klasa z metodami pomocniczymi dla API.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright wlasnosc prywatna, wszelkie prawa zastrzezone, zakaz rozpowszechniania
 * @abstract
 */

##### WERSJA ROZWOJOWA #####

class Helpers
{

    public static function arrayIsAssociative(array $_arr)
    {
        foreach (array_keys($_arr) as $key) {
            if (!is_int($key)) {
                return true;
            }
        }

        return false;
    }

    public static function jsonDecode($_data, $_forceErrorCode = null)
    {
        $data = json_decode($_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException("Decoding JSON error", ($_forceErrorCode ? $_forceErrorCode : json_last_error()));
        }

        return $data;
    }

    public static function jsonEncode($_data, $_forceErrorCode = null)
    {
        $data = json_encode($_data);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException("Encoding JSON error", ($_forceErrorCode ? $_forceErrorCode : json_last_error()));
        }

        return $data;
    }

    public static function soapXmlToArray($_xmlObject)
    {
        if (is_object($_xmlObject) || is_array($_xmlObject)) {
            $ret = array();

            foreach ((array) $_xmlObject as $index => $node) {
                $ret[$index] = ((is_object($_xmlObject) || is_array($_xmlObject)) ? static::soapXmlToArray($node) : $node);
            }

            return $ret;
        }

        return $_xmlObject;
    }

    public static function soapXmlFromArray(array $_arr, \SimpleXMLElement $_xml)
    {
        // nie dodaje tu ns, gdyz biblioteka uzywa wtedy <tag xmlns=ns> zamiast skroconej formy <ns:tag>

        foreach ($_arr as $k => $v) {
            if (is_array($v)) {
                if (static::arrayIsAssociative($v)) {
                    static::soapXmlFromArray($v, $_xml->addChild($k));
                } else {
                    foreach ($v as $vv) {
                        if (is_array($vv)) {
                            static::soapXmlFromArray($vv, $_xml->addChild($k));
                        } else {
                            $_xml->addChild($k, $vv);
                        }
                    }
                }
            } else {
                $_xml->addChild($k, $v);
            }
        }

        return $_xml;
    }

    public static function soapGenerateMessage(array $_)
    {
        // tworzymy node'a z wynikiem metody
        $node = Helpers::soapXmlFromArray($_["methodData"], new \SimpleXMLElement("<{$_["method"]} />"));
        // usuwamy naglowek xml by nie byl zduplikowany
        $nodeText = preg_replace("/<\?xml[^\]]*\?>/", "", $node->asXML());
        // dodaje skroconego ns'a
        $nodeText = preg_replace("/<(\/?)/", "<\\1m:", $nodeText);

        // i opakowujemy w koperte
        return "<?xml version='1.0'?>"
                . "<soap:Envelope xmlns:m='{$_["url"]}' xmlns:soap='http://schemas.xmlsoap.org/soap/envelope/' soap:encodingStyle='http://schemas.xmlsoap.org/soap/encoding/'>"
                . "<soap:Header>"
                . "<m:module>{$_["module"]}</m:module>"
                . "</soap:Header>"
                . "<soap:Body>$nodeText</soap:Body>"
                . "</soap:Envelope>";
    }

}
