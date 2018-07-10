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
     * @var string|array|\Closure 函数名称、一个类或对象的方法名称或匿名函数
     * 示例和参数列表:
     *
     * ```php
     * //通过 Parameters 对象调用 validateParam()
     * function foo($value, $Parameter, $Validator, $param)
     *
     * //直接通过 Validator 对象调用 validateValue()
     * function foo($value, $param)
     * ```
     *
     * - `$value` mixed 参数的值;
     * - `$Parameter` Parameter 参数的对象;
     * - `$Validator` CallableValidator 方法验证器的对象 $this;
     * - `$param` mixed 方法的其他参数;
     */

    public $method;

    /**
     * @var mixed callable 的其他参数。
     */
    public $param;

    /**
     * CompareValidator constructor.
     * @param array $config
     * @throws InvalidConfigException
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        if ($this->method === null)
            throw new InvalidConfigException('The "method" property must be set.');

        if ((is_string($this->method) && !function_exists($this->method)) ||        //函数
            (is_object($this->method) && !($this->method instanceof \Closure)) ||   //匿名函数
            (is_array($this->method) && count($this->method) > 1 && !method_exists($this->method[0], $this->method[1])))    //类或对象的方法
            throw new InvalidConfigException('Invalid method value.');
    }

    /**
     * 验证参数对象
     * @param Parameter $parameter 参数
     * @return bool
     */
    protected function _validateParam(Parameter $parameter)
    {
        $value = $parameter->getValue();

        call_user_func($this->method, $value, $parameter, $this, $this->param);

        return true;
    }

    /**
     * 验证值
     * @param mixed $value 值
     * @return mixed
     */
    protected function _validateValue($value)
    {
        return call_user_func($this->method, $value, $this->param);
    }
}
