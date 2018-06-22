<?php
namespace liyuze\validator\tests;

use liyuze\validator\Exceptions\Exception;

trait ReflectionTrait
{
    /**
     * @param $object
     * @param string $property
     * @return mixed
     */
    public function getPrivateProperty($object, $property)
    {
        try {
            $class = new \ReflectionClass($object);
        } catch (\ReflectionException $e) {
            return null;
        }
        $_property = $class->getProperty($property);
        $_property->setAccessible(true);
        return $_property->getValue($object);
    }

    /**
     * @param $object
     * @param string $property
     * @param mixed $value
     * @return mixed
     */
    public function setPrivateProperty($object, $property, $value)
    {
        try {
            $class = new \ReflectionClass($object);
        } catch (\ReflectionException $e) {
            return null;
        }
        $_property = $class->getProperty($property);
        $_property->setAccessible(true);
        $_property->setValue($object, $value);
    }

    protected function callPrivateMethod($Object, $method, $args = [])
    {
        try {
            $method = new \ReflectionMethod(get_class($Object), $method);
        } catch (\ReflectionException $e) {
            return false;
        }
        $method->setAccessible(true);
        return $method->invokeArgs($Object, $args);
    }
}