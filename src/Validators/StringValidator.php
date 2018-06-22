<?php

namespace liyuze\validator\Validators;

use liyuze\validator\Parameters\Parameter;

class StringValidator extends Validator
{
    /**
     * @var string 验证器名称
     */
    protected $_name = 'string';

    /**
     * @var int 长度最小值.
     */
    public $minLength;

    /**
     * @var int 长度最大值.
     */
    public $maxLength;

    /**
     * @var string 文字编码
     */
    public $encoding = 'UTF-8';

    /**
     * @var string 错误消息
     */
    public $message = '';
    public $messageMinLength = '';
    public $messageMaxLength = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->message == '' && $this->message = '{param_name}不是一个字符串';
        $this->messageMinLength == '' && $this->messageMinLength = '{param_name}的字符串长度不能小于{min}';
        $this->messageMaxLength == '' && $this->messageMaxLength = '{param_name}的字符串长度不能大于{max}';
    }

    /**
     * 验证参数对象
     * @param Parameter $parameter 参数
     * @return mixed
     */
    protected function _validateParam(Parameter $parameter)
    {
        $value = $parameter->getValue();

        if (!is_string($value))
            $this->addError($parameter, $this->message);

        $length = mb_strlen($value, $this->encoding);

        //最大长度验证
        if ($this->maxLength !== null && $length > $this->maxLength) {
            $this->addError($parameter, $this->messageMaxLength, ['max' => $this->maxLength], 'max');
        }

        //最小长度验证
        if ($this->minLength !== null && $length < $this->minLength) {
            $this->addError($parameter, $this->messageMinLength, ['min' => $this->minLength], 'min');
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
        //验证
        if (!is_string($value))
            return $this->message;

        $length = mb_strlen($value, $this->encoding);

        //最大长度验证
        if ($this->maxLength !== null && $length > $this->maxLength) {
            return [$this->messageMaxLength, ['max' => $this->maxLength]];
        }

        //最小长度验证
        if ($this->minLength !== null && $length < $this->minLength) {
            return [$this->messageMinLength, ['min' => $this->minLength]];
        }

        return true;
    }
}
