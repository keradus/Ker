<?php

// tworze A use Singleton, B use Singleton, $a = A::getInstance(), $b = B::getInstance() - czy jest to ta sama czy inna instancja ???

namespace Ker;

trait SingletonTrait
{
    /**
     * Create Singleton instance.
     *
     * @return Singleton instance
     */
    public static function getInstance()
    {
        static $instance = null;

        if ($instance === null) {
            $instance = new static();
        }

        return $instance;
    }

    /**
     * Prevent creating Singleton instance by "new" keyword.
     */
    protected function __construct()
    {
        // TODO: what about parameters?
    }

    /**
     * Prevent cloning Singleton instance.
     */
    protected function __clone()
    {
    }

    /**
     * Prevent unserializing Singleton instance.
     */
    protected function __wakeup()
    {
    }
}
