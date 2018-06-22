<?php

namespace liyuze\validator\Validators;

use liyuze\validator\Exceptions\InvalidConfigException;
use liyuze\validator\Parameters\Parameter;

class MatchValidator extends Validator
{
    /**
     * @var string 验证器名称
     */
    protected $_name = 'match';

    /**
     * @var string 正则表达式
     */
    public $pattern;

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
        if ($this->pattern === null)
            throw new InvalidConfigException('The "pattern" property must be set.');

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

        if ($this->not xor !preg_match($this->pattern, $value))
            $this->addError($parameter, $this->message);

        return true;
    }


    /**
     * 验证值
     * @param mixed $value 值
     * @return mixed
     */
    protected function _validateValue($value)
    {
        if ($this->not xor !preg_match($this->pattern, $value))
            return $this->message;

        return true;
    }
}
