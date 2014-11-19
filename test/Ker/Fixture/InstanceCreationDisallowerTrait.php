<?php

namespace Ker\Fixture;

class InstanceCreationDisallowerTrait
{
    use \Ker\InstanceCreationDisallowerTrait;

    public static function createInstance()
    {
        return new static();
    }
}
