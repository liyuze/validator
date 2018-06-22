<?php

namespace liyuze\validator\Validators;

use liyuze\validator\Parameters\Parameter;

class EmailValidator extends Validator
{
    /**
     * @var string 验证器名称
     */
    protected $_name = 'email';

    /**
     * @var string 包含名称的email验证规则
     * @see http://www.regular-expressions.info/email.html
     */
    public $pattern = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';

    /**
     * @var string 包含名称的email验证规则
     * 当 [[allowName]] 是 true 时启用该验证规则.
     * @see allowName
     */
    public $fullPattern = '/^[^@]*<[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?>$/';

    /**
     * @var boolean 是否允许包含名称 (e.g. "John Smith <john.smith@example.com>"). 默认false.
     * @see fullPattern
     */
    public $allowName = false;

    /**
     * @var string 错误消息
     */
    public $message = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->message == '' && $this->message = '{param_name}不是有效的Email';
    }

    /**
     * 验证参数对象
     * @param Parameter $parameter 参数
     * @return bool
     */
    protected function _validateParam(Parameter $parameter)
    {
        $value = $parameter->getValue();

        if (!(preg_match($this->pattern, $value) || $this->allowName && preg_match($this->fullPattern, $value))) {
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
        if (!(preg_match($this->pattern, $value) || $this->allowName && preg_match($this->fullPattern, $value))) {
            return $this->message;
        }

        return true;
    }
}
