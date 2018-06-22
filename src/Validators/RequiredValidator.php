<?php

namespace liyuze\validator\Validators;

use liyuze\validator\Parameters\Parameter;

class RequiredValidator extends Validator
{
    /**
     * @var string 验证器名称
     */
    protected $_name = 'required';

    /**
     * @var bool 空值是否跳过验证
     */
    public $skipIsEmpty = false;

    /**
     * @var string 错误消息
     */
    public $message = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->message == '' && $this->message = '{param_name}不能为空';
    }

    /**
     * 验证参数对象
     * @param Parameter $parameter 参数
     * @return mixed
     */
    protected function _validateParam(Parameter $parameter)
    {
        $value = $parameter->getValue();
        $value = is_string($value) ? trim($value) : $value;
        if ($this->isEmpty($value)) {
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
        $value = is_string($value) ? trim($value) : $value;
        if ($this->isEmpty($value)) {
            return $this->message;
        }

        return true;
    }
}
