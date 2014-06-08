<?php

namespace Ker\Test;

class InstanceCreationDisallowerTrait extends \Ker\PHPUnit\TestCase
{
    public function testCreation()
    {
        $reflection = new \ReflectionClass($this->getFixtureName());
        $constructorReflection = $reflection->getConstructor();

        $this->assertTrue($constructorReflection && $constructorReflection->isProtected());
    }

    /**
     * @expectedException LogicException
     */
    public function testCreationByHelper()
    {
        $name = $this->getFixtureName();
        $instance = $name::createInstance();
    }

    public function testExtCreation()
    {
        $reflection = new \ReflectionClass($this->getFixtureName() . "\\Extended");
        $constructorReflection = $reflection->getConstructor();

        $this->assertTrue($constructorReflection && $constructorReflection->isProtected());
    }
}
