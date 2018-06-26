<?php

namespace liyuze\validator\Validators;

use liyuze\validator\Exceptions\InvalidConfigException;
use liyuze\validator\Parameters\Parameter;

class InValidator extends Validator
{
    /**
     * @var string 验证器名称
     */
    protected $_name = 'in';

    /**
     * @var array 范围值.
     */
    protected $_range;

    /**
     * @var bool 验证模式，当为 true 时验证值和类型全部相等。默认 true。
     */
    public $strict = false;

    /**
     * @var bool 不在给定的范围内。
     */
    public $not = false;

    /**
     * @var string 错误消息
     */
    public $message = '';


    /**
     * InValidator constructor.
     * @param $config
     * @throws InvalidConfigException
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        //必配置项
        if ($this->_range === null)
            throw new InvalidConfigException('The "range" property must be set.');

        $this->message == '' && $this->message = '{param_name}的值是无效的。';
    }

    /**
     * 验证参数对象
     * @param Parameter $parameter 参数
     * @return mixed
     */
    protected function _validateParam(Parameter $parameter)
    {
        $value = $parameter->getValue();

        if (is_array($value) || $value instanceof \Traversable) {
            foreach ($value as $v) {
                if ($this->not xor !in_array($v, $this->_range, $this->strict))
                    $this->addError($parameter, $this->message);
            }
        } else {
            if ($this->not xor !in_array($value, $this->_range, $this->strict))
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
        if (is_array($value) || $value instanceof \Traversable) {
            foreach ($value as $v) {
                if ($this->not xor !in_array($v, $this->_range, $this->strict))
                    return $this->message;
            }
        } else {
            if ($this->not xor !in_array($value, $this->_range, $this->strict))
                return $this->message;
        }

        return true;
    }

    /**
     * range set方法
     * @param array $value
     * @throws InvalidConfigException
     */
    public function setRange($value)
    {
        if (!is_array($value) || empty($value))
            throw new InvalidConfigException('Invalid range value.');

        $this->_range = $value;
    }
}
