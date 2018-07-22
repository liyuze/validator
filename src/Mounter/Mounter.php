<?php

namespace liyuze\validator\Mounter;
use liyuze\validator\Parameters\Parameter;

/**
 * Class Mounter
 * @package liyuze\validator\Mounter
 */
abstract class Mounter
{
    /**
     * @var bool 是否缓存运算结果，默认是true。
     */
    public $cache = true;

    /**
     * @var Parameter 参数对象。
     */
    private $_parameter;

    public function __construct(Parameter $parameter, $config = [])
    {
        $this->_parameter = $parameter;

        foreach ($config as $field => $value) {
            $this->_set($field, $value);
        }
    }

    /**
     * 动态设置属性值
     * @param $field
     * @param $value
     */
    private function _set($field, $value)
    {
        //索引值 或 私有变量
        if (is_int($field) || strpos($field, '_') !== false) return;

        if (method_exists($this, $method = 'set'.ucfirst($field))) {  //私有变量调用set方法赋值
            $this->$method($value);
        } elseif (property_exists($this, $field)) {
            $this->$field = $value;
        }

        return;
    }

    /**
     * @return Parameter
     */
    protected function getParameter()
    {
        return $this->_parameter;
    }

    /**
     * 注册挂载值对应的key值。
     * @return array 格式为[挂载的名称]
     */
    public abstract function registerKeys();

    /**
     * 挂载器执行
     * @return array 应返回数组，格式为[挂载的名称=> 挂载值]，挂载的名称必须在 [registerKeys()] 返回的数组中指出。
     */
    public abstract function run();
}