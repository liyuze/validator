<?php
namespace liyuze\validator\Validators;

use liyuze\validator\Exceptions\InvalidArgumentException;
use liyuze\validator\Parameters\Parameter;

abstract class Validator
{
    const VALIDATE_STATUS_WAITING    = 0;    //等待验证
    const VALIDATE_STATUS_DONE       = 1;    //完成验证

    /**
     * @var string 验证器名称
     */
    protected $_name = '';

    /**
     * @var int 验证状态
     * 当状态状态为 VALIDATE_STATUS_DONE 完成验证时，会直接返回验证结果。
     */
    protected $_validateStatus = self::VALIDATE_STATUS_WAITING;

    /**
     * @var bool 当值为空时是否跳过验证，默认是 true
     * 可以通过配置 isEmpty 属性来实现自定义为空的验证方法。
     */
    public $skipIsEmpty = true;

    /**
     * @var callable 自定义验证是否为空的方法,返回boolean类型。
     * 格式：
     * function($value){}
     */
    public $isEmpty;

    /**
     * 配置属性
     * Validator constructor.
     * @param $config
     */
    public function __construct($config = [])
    {
        foreach ($config as $field => $value) {
            $this->_set($field, $value);
        }
    }

    /**
     * 动态设置属性值
     * @param $field
     * @param $value
     */
    public function __set($field, $value)
    {
        $this->_set($field, $value);
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
     * 设置验证状态
     * @param $status
     */
    public function updateValidateStatus($status)
    {
        if (!in_array($status, [self::VALIDATE_STATUS_WAITING, self::VALIDATE_STATUS_DONE], true)) {
            $status = self::VALIDATE_STATUS_WAITING;
        }


        $this->_validateStatus = $status;
    }

    /**
     * 判断值是否为空。
     * 默认判断如果值为 null、[]、’‘ 则返回true，可以通过配置 isEmpty 参数
     * @param $value
     * @return bool|mixed
     */
    public function isEmpty($value)
    {
        if (is_callable($this->isEmpty))
            return call_user_func($this->isEmpty, $value);

        if (is_array($this->isEmpty))
            return in_array($value , $this->isEmpty, true);

        return $value === null || $value === [] || $value === '';
    }

    /**
     * 验证参数集合的某个参数
     * @param Parameter $parameter 参数对象
     * @return boolean true跳过验证
     */
    public function validateParam(Parameter $parameter)
    {
        //已验证
        if ($this->_validateStatus === self::VALIDATE_STATUS_DONE)
            return true;

        //值为空时跳过验证
        if ($this->skipIsEmpty && $this->isEmpty($parameter->getValue())) {
            //设置验证状态
            $this->updateValidateStatus(self::VALIDATE_STATUS_DONE);
            return true;
        }

        //具体的逻辑验证
        $this->_validateParam($parameter);

        //设置验证状态
        $this->updateValidateStatus(self::VALIDATE_STATUS_DONE);

        return false;
    }

    /**
     * 验证参数集合的某个参数
     * @param Parameter $parameter 参数对象
     * @return boolean true跳过验证
     */
    protected abstract function _validateParam(Parameter $parameter);

    /**
     * 验证值,返回错误消息
     * @param mixed $value 值
     * @param mixed $error 返回的验证错误消息
     * @return mixed
     */
    public function validate($value, &$error = null)
    {
        $result = $this->_validateValue($value);
        if ($result !== true) {
            if (is_string($result))
                $result = [$result, []];

            list($message, $params) = $result;
            $error = $this->formatMessage($message, $params);

            return false;
        }
        $error = '';
        return true;
    }


    /**
     * 格式化消息
     * @param $message 消息模板
     * @param array $params 消息参数
     * @return string
     */
    private function formatMessage($message, $params = [])
    {
        $placeholders = [];
        $params['param_name'] = '该输入';
        foreach ($params as $name => $value) {
            $placeholders['{'.$name.'}'] = $value;
        }

        return empty($placeholders) ? $message : strtr($message, $placeholders);
    }

    /**
     * 验证值
     * @param $value
     * @return mixed
     */
    protected abstract function _validateValue($value);

    /**
     * 新增错误消息
     * @param Parameter $parameter 参数
     * @param string $error_template 验证消息模板
     * @param array $params 验证消息参数
     * @param null|string $validate_name 验证名称
     */
    public function addError(Parameter $parameter, $error_template, $params = [], $validate_name = null )
    {
        $validate_name = $validate_name === null ? $this->_name : $this->_name.'-'.$validate_name;
        $parameter->getParameters()->addError($parameter->getName(), $validate_name, $error_template, $params);
    }
}