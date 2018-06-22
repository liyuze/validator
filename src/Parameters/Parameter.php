<?php

namespace liyuze\validator\Parameters;
use liyuze\validator\Validators\Validator;
use liyuze\validator\Validators\ValidatorCreator;

/**
 * 参数
 * Class Parameter
 * @package parameters
 */
class Parameter
{
    /**
     * Parameter constructor.
     * @param Parameters $parameters 参数集对象
     * @param string $name 名称
     * @param mixed $value 值
     * @param string $alias 别名
     */
    public function __construct(Parameters $parameters, $name, $value = null, $alias = null)
    {
        $this->_name = $name;
        $this->_value = $value;
        $this->_alias = $alias;
        $this->_parameters = $parameters;
    }

    /**
     * @var Parameters 参数集对象
     */
    private $_parameters = null;

    /**
     * @var string 参数名称
     */
    private $_name = null;

    /**
     * @var string 别名
     */
    private $_alias;

    /**
     * @var void 参数值
     */
    private $_value = null;

    private $_validatorConfig = [];

    /**
     * @var array(Validator) 验证器列表
     */
    private $_validators = [];


    //region 参数相关

    /**
     * 获取名
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * 设置值
     * @param mixed $value
     * @param boolean $reset 是否重置验证状态和清除错误消息
     */
    public function setValue($value, $reset = true)
    {
        if ($reset) {
            //重置验证器的验证状态
            $this->resetValidateStatus();
            //清楚该参数的错误消息
            $this->_parameters->clearErrors($this->_name);
        }
        $this->_value = $value;
    }

    /**
     * 获取值
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * 获取别名
     * @return string
     */
    public function getAliasOrName()
    {
        return $this->_alias === null ? $this->_name : $this->_alias;
    }

    /**
     * 获取参数集对象
     * @return Parameters
     */
    public function getParameters()
    {
        return $this->_parameters;
    }


    //endregion


    //region 验证器相关

    /**
     * 设置验证器配置
     * @param array $config
     *
     * 例：
     * [验证规则]
     *
     * 验证规则格式如下：
     * [验证简称 | 匿名函数 | 可调用函数 [, '参数' => ’值, '参数' => ’值]]
     * 例：
     * ['required']
     * ['number', 'max' => 10, 'min' => 1]
     * [function ($parameters, $param_name){...}]
     * ['\app\module\UserModel::checkUserName']
     *
     */
    public function setValidatorConfig(array $config)
    {
        foreach ($config as $validator_config) {
            $this->addValidator(ValidatorCreator::create($validator_config[0], array_slice($validator_config, 1)));
            $this->_validatorConfig[] = $validator_config;
        }
    }

    /**
     * 获取验证规则
     * @return array
     */
    public function getValidatorConfig()
    {
        return $this->_validatorConfig;
    }

    /**
     * 新增验证器
     * @param Validator $Validator
     */
    private function addValidator(Validator $Validator)
    {
        $this->_validators[] = $Validator;
    }

    //endregion

    //region 验证相关

    /**
     * 执行所有验证器
     */
    public function validate()
    {
        foreach ($this->_validators as $validator) {
            $validator->validateParam($this);
        }
    }

    //重置验证器的验证状态
    public function resetValidateStatus ()
    {
        foreach ($this->_validators as $v) {
            /**
             * @var Validator $v
             */
            $v->updateValidateStatus(Validator::VALIDATE_STATUS_WAITING);
        }
    }

    //endregion
}