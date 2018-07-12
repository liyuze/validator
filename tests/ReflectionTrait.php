<?php
namespace liyuze\validator\tests;

use liyuze\validator\Exceptions\Exception;

/**
 * 反射
 * Trait ReflectionTrait
 * @package liyuze\validator\tests
 */
trait ReflectionTrait
{
    /**
     * 获取私有属性
     * @param \stdClass $object 对象
     * @param string $property 属性名称
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
     * 设置私有属性
     * @param \stdClass $object 对象
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

    /**
     * 调用私有方法
     * @param string|\stdClass $Object 对象或类
     * @param string $method 方法名称
     * @param array $args 参数
     * @return bool|mixed
     */
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