<?php

namespace liyuze\validator\Validators;

use liyuze\validator\Exceptions\InvalidConfigException;
use liyuze\validator\Parameters\Parameter;

class CallableValidator extends Validator
{
    /**
     * @var string 验证器名称
     */
    protected $_name = 'callable';

    /**
     * @var string|\Closure 匿名方法、函数名称或一个类或对象的方法名称
     * 示例和参数列表:
     *
     * ```php
     * function foo($value, $parameter, $methodValidator)
     * ```
     *
     * - `$value` 参数的值;
     * - `$parameter` 参数的对象;
     * - `$methodValidator` 方法验证器的对象 $this.
     */

    public $method;

    /**
     * @var \stdClass|string 调用的对象或类
     */
    public $target;

    /**
     * CompareValidator constructor.
     * @param array $config
     * @throws InvalidConfigException
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        if ($this->target !== null) {
            if (!is_object($this->target) && !class_exists($this->target))
                throw new InvalidConfigException('Invalid target value.');
            elseif (!method_exists($this->target, $this->method))
                throw new InvalidConfigException('Invalid method value.');
        } else {
            if ($this->method === null) {
                throw new InvalidConfigException('The "method" property must be set.');
            } elseif (!$this->method instanceof \Closure &&
                !(is_string($this->method) && function_exists($this->method))) {
                throw new InvalidConfigException('Invalid method value.');
            }
        }
    }

    /**
     * 验证参数对象
     * @param Parameter $parameter 参数
     * @return bool
     */
    protected function _validateParam(Parameter $parameter)
    {
        $value = $parameter->getValue();

        $method = $this->method;
        if (is_string($method))  {
            if ($this->target !== null)
                $method = [$this->target, $this->method];
        }
        call_user_func($method, $value, $parameter, $this);

        return true;
    }

    /**
     * 验证值
     * @param mixed $value 值
     * @return mixed
     */
    protected function _validateValue($value)
    {
        $method = $this->method;
        if (is_string($method))  {
            if ($this->target !== null)
                $method = [$this->target, $this->method];
        }
        call_user_func($method, $value);

        return true;
    }
}
