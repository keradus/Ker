<?php

namespace Ker\Test;

class InaccessiblePropertiesProtectorTrait extends \Ker\PHPUnit\TestCase
{
    protected $fixture;

    public function setUp()
    {
        $name = $this->getFixtureName();
        $this->fixture = new $name();
    }

    /**
     * @expectedException LogicException
     */
    public function testGetInaccessiblePropertyWithTrait()
    {
        $bar = $this->fixture->bar;
    }

    /**
     * @expectedException LogicException
     */
    public function testSetInaccessiblePropertyWithTrait()
    {
        $this->fixture->bar = 1;
    }

    /**
     * @expectedException LogicException
     */
    public function testIssetInaccessiblePropertyWithTrait()
    {
        $isset = isset($this->fixture->bar);
    }
}
