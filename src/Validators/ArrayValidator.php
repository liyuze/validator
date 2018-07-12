<?php

namespace liyuze\validator\Validators;

use liyuze\validator\Parameters\Parameter;

class ArrayValidator extends Validator
{
    /**
     * @var string 验证器名称
     */
    protected $_name = 'array';

    /**
     * @var string|array 验证key验证器配置
     * @see 配置信息查看 [[\liyuze\validator\Parameters::parseValidatorConfig()]] 说明
     */
    public $keyValidateConfig;

    /**
     * @var string|array 验证value验证器配置
     * @see 配置信息查看 [[\liyuze\validator\Parameters::parseValidatorConfig()]] 说明
     */
    public $valueValidateConfig;

    public $stopFirstError = true;

    /**
     * @var string 错误消息
     */
    public $message = '';
    public $messageKey = '';
    public $messageValue = '';

    /**
     * 判断值是否为空。
     * 默认判断如果值为 null、[] 则返回true，可以通过配置 isEmpty 参数
     * @param $value
     * @return bool|mixed
     */
    public function isEmpty($value)
    {
        if (is_callable($this->isEmpty))
            return call_user_func($this->isEmpty, $value);

        return $value === null || $value === [];
    }

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->message == '' && $this->message = '{param_name}的值必须是数组类型。';
        $this->messageKey == '' && $this->messageKey = '{param_name}的值的key值的格式不正确。';
        $this->messageValue == '' && $this->messageValue = '{param_name}的值的value值的格式不正确。';
    }

    /**
     * 验证参数对象
     * @param Parameter $parameter 参数
     * @return bool
     */
    protected function _validateParam(Parameter $parameter)
    {
        $value = $parameter->getValue();

        //类型验证
        if (!is_array($value) && ! $value instanceof \ArrayAccess) {
            $this->addError($parameter, $this->message);
            return false;
        }

        //key-value 验证器创建
        $keyValidators = $valueValidators = null;
        if ($this->keyValidateConfig !== null) {
            $validateConfig = $this->parseValidatorConfig($this->keyValidateConfig);
            foreach ($validateConfig as $config)
                $keyValidators[] = ValidatorCreator::create($config[0], array_slice($config, 1));
        }
        if ($this->valueValidateConfig !== null) {
            $validateConfig = $this->parseValidatorConfig($this->valueValidateConfig);
            foreach ($validateConfig as $config)
                $valueValidators[] = ValidatorCreator::create($config[0], array_slice($config, 1));
        }

        if ($keyValidators !== null || $valueValidators !== null) {
            foreach ($value as $k => $v) {

                $has_error = false;
                if ($keyValidators !== null) {
                    $key_message = '';
                    foreach ($keyValidators as $kk => $validator) {
                        if ($validator->validate($k, $key_message) !== true)
                        $this->addError($parameter, $this->messageKey, ['key_message'=> $key_message], 'key-'.$k.'_'.$kk);
                        $has_error = true;
                    }
                }

                if ($valueValidators !== null) {
                    foreach ($valueValidators as $kk => $validator) {
                        $value_message = '';
                        if ($validator->validate($v, $value_message) !== true)
                            $this->addError($parameter, $this->messageValue, ['value_message'=> $value_message], 'value-'.$k.'_'.$kk);
                        $has_error = true;
                    }
                }

                //跳出验证
                if ($this->stopFirstError && $has_error) {
                    break;
                }
            }
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
        //类型验证
        if (!is_array($value) && ! $value instanceof \ArrayAccess) {
            return $this->message;
        }

        //key-value 验证
        $keyValidators = $valueValidators = null;
        if ($this->keyValidateConfig !== null) {
            $validateConfig = $this->parseValidatorConfig($this->keyValidateConfig);
            foreach ($validateConfig as $config)
                $keyValidators[] = ValidatorCreator::create($config[0], array_slice($config, 1));
        }
        if ($this->valueValidateConfig !== null) {
            $validateConfig = $this->parseValidatorConfig($this->valueValidateConfig);
            foreach ($validateConfig as $config)
                $valueValidators[] = ValidatorCreator::create($config[0], array_slice($config, 1));
        }

        if ($keyValidators !== null || $valueValidators !== null) {
            $message = '';
            foreach ($value as $k => $v) {
                if ($keyValidators !== null) {
                    foreach ($keyValidators as $kk => $validator) {
                        if ($validator->validate($k, $message) !== true)
                            return [$this->messageKey, ['key_message'=> $message]];
                    }
                }

                if ($valueValidators !== null) {
                    foreach ($valueValidators as $kk => $validator) {
                        if ($validator->validate($v, $message) !== true)
                            return [$this->messageValue, ['value_message'=> $message]];
                    }
                }
            }
        }

        return true;
    }

    private function parseValidatorConfig($data)
    {
        $validators_config = [];
        //单个验证器
        if (is_string($data)) {
            $validators_config[] = [$data];

            //多个验证器
        } elseif (is_array($data)) {
            $validator_index = -1;
            foreach ($data as $k => $v) {
                if (is_int($k)) {
                    //验证器
                    if (is_array($v)) {
                        $temp_config = $this->parseValidatorConfig($v);
                        $validator_index += count($temp_config);
                        $validators_config = array_merge($validators_config, $temp_config);
                    } else {
                        $validator_index++;
                        $validators_config[$validator_index][0] = $v;
                    }
                } else {
                    //参数
                    $validators_config[$validator_index][$k] = $v;
                }
            }
        } else {
            //todo 错误配置
        }

        return $validators_config;
    }

}
