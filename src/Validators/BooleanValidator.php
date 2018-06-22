<?php

namespace liyuze\validator\Validators;

use liyuze\validator\Parameters\Parameter;

class BooleanValidator extends Validator
{
    /**
     * @var string 验证器名称
     */
    protected $_name = 'boolean';

    /**
     * @var bool 验证模式，当为 true 时使用 === 进行 $true_value 和 $false_value 验证。默认 true。
     */
    public $strict = true;

    /**
     * @var bool true value
     */
    public $trueValue = true;

    /**
     * @var bool false value
     */
    public $falseValue = false;

    /**
     * @var string 错误消息
     */
    public $message = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->message == '' && $this->message = '{param_name}必须是布尔类型';
    }

    /**
     * 验证参数对象
     * @param Parameter $parameter 参数
     * @return bool
     */
    protected function _validateParam(Parameter $parameter)
    {
        $value = $parameter->getValue();

        if ($this->strict) {
            $valid = $value === $this->trueValue || $value === $this->falseValue;
        } else {
            $valid = $value == $this->trueValue || $value == $this->falseValue;
        }

        if (!$valid) {
            $this->addError($parameter, $this->message);
        }

        return true;
    }

    /**
     * 验证值
     * @param mixed $value 值
     * @return mixed
     */
    protected function _validateValue($value)
    {
        if ($this->strict) {
            $valid = $value === $this->trueValue || $value === $this->falseValue;
        } else {
            $valid = $value == $this->trueValue || $value == $this->falseValue;
        }

        if (!$valid) {
            return $this->message;
        }

        return true;
    }
}
