<?php
namespace Ker\Test;

class InaccessiblePropertiesProtectorTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException LogicException
     */
    public function testGetInaccessiblePropertyWithTrait()
    {
        $testClass = new TestClass();
        $bar = $testClass->bar;
    }

    /**
     * @expectedException LogicException
     */
    public function testSetInaccessiblePropertyWithTrait()
    {
        $testClass = new TestClass();
        $testClass->bar = 1;
    }

    /**
     * @expectedException LogicException
     */
    public function testIssetInaccessiblePropertyWithTrait()
    {
        $testClass = new TestClass();
        $isset = isset($testClass->bar);
    }
}

class TestClass
{
    use \Ker\InaccessiblePropertiesProtectorTrait;

    public $foo;
}
