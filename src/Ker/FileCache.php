<?php

//TASK: #49 - dokumentacja

namespace Ker;

abstract class FileCache
{

    public static $items = array(/* name => path */);
    public static $container = array();

    public static function get($_name)
    {
        if (!isset(static::$container[$_name])) {
            static::deserialize($_name);
        }

        return static::$container[$_name];
    }

    public static function set($_name, $_data)
    {
        static::serialize($_name, $_data);
        static::$container[$_name] = $_data;
    }

    public static function deserialize($_name)
    {
        if (!isset(static::$items[$_name])) {
            throw new \InvalidArgumentException("Unknown name $_name!");
        }

        static::$container[$_name] = unserialize(file_get_contents(static::$items[$_name]));
    }

    public static function serialize($_name, $_data)
    {
        if (!isset(static::$items[$_name])) {
            throw new \InvalidArgumentException("Unknown name $_name!");
        }

        file_put_contents(static::$items[$_name], serialize($_data));
    }

}
