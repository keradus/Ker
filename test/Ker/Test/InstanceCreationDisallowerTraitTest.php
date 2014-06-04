<?php

namespace Ker\Test;

class InstanceCreationDisallowerTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testCreation()
    {
        $reflection = new \ReflectionClass("\Ker\Test\DisallowedClass");
        $constructorReflection = $reflection->getConstructor();

        $this->assertTrue($constructorReflection && $constructorReflection->isProtected());
    }

    /**
     * @expectedException LogicException
     */
    public function testCreationByHelper()
    {
        $instance = DisallowedClass::createInstance();
    }

    public function testExtCreation()
    {
        $reflection = new \ReflectionClass("\Ker\Test\DisallowedExtClass");
        $constructorReflection = $reflection->getConstructor();

        $this->assertTrue($constructorReflection && $constructorReflection->isProtected());
    }
}

class DisallowedClass
{
    use \Ker\InstanceCreationDisallowerTrait;

    public static function createInstance()
    {
        return new static();
    }
}

class DisallowedExtClass extends DisallowedClass
{
}
