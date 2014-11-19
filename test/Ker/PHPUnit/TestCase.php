<?php

namespace Ker\PHPUnit;

class TestCase extends \PHPUnit_Framework_TestCase
{
    public function getFixtureName()
    {
        return str_replace("Ker\\Test\\", "Ker\\Fixture\\", get_called_class());
    }
}
