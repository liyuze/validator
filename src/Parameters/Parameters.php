<?php

namespace liyuze\validator\Parameters;
use liyuze\validator\Common\CreatorTrait;
use liyuze\validator\Creator;
use liyuze\validator\ValidatorComponent;

/**
 * 参数集
 * 内部存储多个参数对象和验证的统一配置。
 * Class Parameters
 * @package parameters
 */
class Parameters
{
    //region 基础

    /**
     * @var CreatorTrait 创建器
     */
    private $_creator;

    /**
     * @var array 参数对象数组
     */
    private $_parameters = [];

    /**
     * @var array 参数错误数组
     * 格式：[参数名 => [[验证错误信息]...]]
     */
    private $_errors = [];

    /**
     * @var bool 在有验证错误的情况下是否还验证待验证的参数
     */
    public $validateAllParams = false;

    /**
     * @var bool 加载默认的验证器
     */
    public $defaultValidate = true;

    /**
     * Parameters constructor.
     * @param array $params 参数列表
     * @param Creator|ValidatorComponent|null $Creator 创建器
     * 格式：[参数名 => 参数值]
     */
    public function __construct($params = [], $Creator = null)
    {
        if (!empty($params))
            $this->addParams($params);

        if ($Creator === null)
            $Creator = new Creator();

        $this->_creator = $Creator;
    }

    /**
     * 实现通过对象访问发访问参数对象
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getParam($name);
    }

    //endregion

    //region 配置相关

    public function setParams($params){
        $this->clearParams();
        $this->addParams($params);
    }
    public function setParamRules(){}

    /**
     * 参数以及验证规则配置
     * @param array $config 配置
     * 格式：[参数名 => [参数值, 验证器配置]]
     * 验证器配置格式查看[[parseValidatorConfig()]]
     * @param null|bool $validateAllParams 在有验证错误的情况下是否还验证待验证的参数
     * @see parseValidatorConfig()
     */
    public function config(array $config, $validateAllParams = null)
    {
        foreach ($config as $param_name => $v) {
            list($param_value, $validate_rule, $alias) = array_pad((array)$v, 3, null);
            $this->addParam($param_name, $param_value, $alias);
            $this->addValidator($param_name, $validate_rule);
        }

        if (isset($validateAllParams))
            $this->validateAllParams = $validateAllParams;
    }

    //endregion

    //region 参数相关

    /**
     * 新增多个参数
     * @param array $params
     */
    public function addParams($params)
    {
        foreach ($params as $param_name => $param_value) {
            list($value, $alias) = array_pad((array)$param_value,2, null);
            $this->addParam($param_name, $value, $alias);
        }
    }

    /**
     * 新增单个参数
     * @param string $param_name 参数名称
     * @param void $value 值
     * @param string $alias 别名
     * @return $this
     */
    public function addParam($param_name, $value = null, $alias = null)
    {
        //todo 参数名有效值验证
        if (!$this->hasParam($param_name)) {
            $this->_parameters[$param_name] = new Parameter($this, $param_name, $value, $alias);
        }

        return $this;
    }

    /**
     * 是否含有某个参数
     * @param string $param_name 参数名
     * @return bool
     */
    public function hasParam($param_name)
    {
        return isset($this->_parameters[$param_name]);
    }

    /**
     * 获取某个参数
     * @param string $param_name 参数名
     * @return null|Parameter
     */
    public function getParam($param_name)
    {
        return $this->hasParam($param_name) ? $this->_parameters[$param_name] : null;
    }

    /**
     *  清空参数
     */
    public function clearParams()
    {
        $this->_parameters = [];
    }

    /**
     * 变更参数值
     * @param $param_name
     * @param $value
     */
    public function setParamsValue($param_name, $value)
    {
        if ($this->hasParam($param_name)) {
            $param = $this->getParam($param_name);
            $param->setValue($value);
        } else {
            $this->addParam($param_name, $value);
        }
    }

    /**
     * 获取指定N个参数的值，不指定返回所有参数的值
     * @param array $params 指定的参数名称
     * @return array
     */
    public function getParamsValue(array $params = [])
    {
        $data = [];

        if (empty($params))
            $params = array_keys($this->_parameters);

        foreach ($params as $param_name) {
            $data[$param_name] = $this->getParamValue($param_name);
        }

        return $data;
    }

    /**
     * 获取参数的值
     * @param string $param_name 参数名
     * @return mixed
     */
    public function getParamValue($param_name)
    {
        return $this->hasParam($param_name) ? $this->_parameters[$param_name]->getValue() : null;
    }

    //endregion

    //region 验证器相关

    /**
     * 设置验证器配置
     * @param array $config
     *
     * 格式：
     * ['参数名' => 验证器配置]
     *
     * [验证器配置]值查看 [[parseValidatorConfig()]] 的注释
     *
     * @see parseValidatorConfig()
     * @throws \liyuze\validator\Exceptions\InvalidArgumentException
     */

    public function setRules(array $config)
    {
        foreach ($config as $param_name => $validator_config) {
            $this->addValidator($param_name, $validator_config);
        }
    }

    /**
     * @param string $param_name
     * @param mixed $validator_config
     * @throws \liyuze\validator\Exceptions\InvalidArgumentException
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     */
    public function addValidator($param_name, $validator_config)
    {
        //新增(已存在则跳过)
        $this->addParam($param_name);

        $validators = $this->_creator->createValidators($validator_config);

        $Parameter = $this->getParam($param_name);
        $Parameter->addValidators($validators);
    }

    /**
     * 验证参数
     * @throws \liyuze\validator\Exceptions\InvalidArgumentException
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     */
    public function validate()
    {
        $this->beforeValidate();

        //进行验证
        foreach ($this->_parameters as $parameter) {
            $parameter->validate();
            //验证单个参数
            if (!$this->validateAllParams && $this->hasError()) {
                break;
            }
        }

        return !$this->hasError();
    }

    /**
     * @throws \liyuze\validator\Exceptions\InvalidArgumentException
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     */
    public function beforeValidate()
    {
        //加载默认验证器
        if ($this->defaultValidate === true) {
            $defaultValidateConfig = $this->_creator->defaultValidateConfig;
            if (!empty($defaultValidateConfig)) {
                foreach($this->_parameters as $param_name => $Parameter) {
                   if (key_exists($param_name, $defaultValidateConfig)) {
                       $this->addValidator($param_name, $defaultValidateConfig[$param_name]);
                   }
                }
            }
        }
    }

    //endregion


    //region 验证消息相关

    /**
     * 新增验证错误信息
     * @param string $param_name 参数名
     * @param string $validate_name 验证名
     * @param string $error_template 验证错误信息
     * @param array $params 验证错误参数
     */
    public function addError($param_name, $validate_name, $error_template, $params = [])
    {
        $params['param_name'] = $param_name;
        $keywords = [];
        foreach ($params as $keyword => $value) {
            $keywords['{'.$keyword.'}'] = $value;
        }

        $error = strtr($error_template, $keywords);

        $this->_errors[$param_name][$validate_name] = $error;
    }

    /**
     * 是否含有验证错误信息
     * @param null|string $param_name 参数名
     * @return bool
     */
    public function hasError($param_name = null)
    {
        return $param_name === null ? count($this->_errors) > 0 : isset($this->_errors[$param_name]);
    }

    /**
     * @param null|string $param_name 参数名
     * @return array
     */
    public function getErrors($param_name = null)
    {
        if ($param_name === null)
            return $this->_errors;
        else
            return $this->hasError($param_name) ? $this->_errors[$param_name] : [];

    }

//    /**
//     * hasError
//     * @param null|string $param_name 参数名
//     * @return array|mixed
//     */
//    public function getFirstErrors($param_name = null)
//    {
//        if ($param_name === null) {
//            foreach ($this->_errors as $param_name => $errors) {
//                return $errors;
//            }
//        } else
//            return $this->hasError($param_name) ? $this->_errors[$param_name] : [];
//    }

    /**
     * 获取第一条错误消息内容
     * @param null|string $param_name 参数名
     * @return mixed|string
     */
    public function getFirstErrorMessage($param_name = null)
    {
        if ($param_name === null) {
            $allErrors = $this->getErrors();
            if (!empty($allErrors)) {
                $errors = reset($allErrors);
            }
        } else {
            $errors = $this->getErrors($param_name);
        }

        if (!empty($errors)) {
            return reset($errors);
        }

        return '';
    }

    /**
     * 清除错误消息
     * @param null|string $param_name 参数名，填写时只清楚该参数名的错误信息
     */
    public function clearErrors($param_name = null)
    {
        if ($param_name === null)
            $this->_errors = [];
        else
            unset($this->_errors[$param_name]);
    }

    //endregion


}