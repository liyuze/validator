<?php

namespace liyuze\validator\Parameters;
use liyuze\validator\Exceptions\InvalidArgumentException;
use liyuze\validator\Mounter\Mounter;
use liyuze\validator\Validators\Validator;

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

    /**
     * @var array(Validator) 验证器列表
     */
    private $_validators = [];

    /**
     * @var array(Mounter) 挂载器列表
     */
    private $_mounters = [];

    /**
     * @var array 缓存的挂载器的值列表
     */
    private $_mountValueCache = [];


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
            //清除错误消息
            $this->_parameters->clearErrors($this->_name);
            //清除挂载缓存
            $this->clearMountValueCache();
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
     * 新增验证器
     * @param Validator $validators
     */
    public function addValidators($validators)
    {
        foreach ($validators as $v) {
            $this->addValidator($v);
        }
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

    //region 挂载相关

    /**
     * 新增挂载器
     * @param Mounter $Mounter
     * @throws InvalidArgumentException
     */
    public function addMounter($Mounter)
    {
        $keys = $Mounter->registerKeys();

        if (!is_array($keys) || empty($keys))
            throw new InvalidArgumentException('Invalid registerKeys() return value.');

        if (!empty(array_intersect($keys, array_keys($this->_mounters))))
            throw new InvalidArgumentException('Register keys (' . implode(',', $keys) . ') already exists.');

        foreach ($keys as $key) {
            $this->_mounters[$key] = $Mounter;
        }
    }

    /**
     * @param string $name 挂载名称
     * @param bool $mustLatest 必须获取最新的运算值，默认是 false 有缓存则读取缓存。
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getMountValue($name, $mustLatest = false)
    {
        //返回缓存值
        if ($mustLatest === false && key_exists($name, $this->_mountValueCache)) {
            return $this->_mountValueCache[$name];
        }
        //获取关联的挂载器
        if (!key_exists($name, $this->_mounters))
            throw new InvalidArgumentException('Invalid mount name value.');
        /**
         * @var Mounter
         */
        $Mounter = $this->_mounters[$name];
        //运行挂载器
        $value = $Mounter->run();
        //校验返回值过滤掉非注册的值
        $value = $this->checkMountValue($Mounter, $value);
        //缓存结果
        if ($Mounter->cache) {
            $this->setMountValueCache($value);
        }
        return $value[$name];
    }

    /**
     * 校验挂载的挂载值是否超出注册的列表。
     * @param Mounter $Mounter
     * @param array $value
     * @return array
     * @throws InvalidArgumentException
     */
    private function checkMountValue($Mounter, $value)
    {
        if (!is_array($value))
            throw new InvalidArgumentException('Mount value must be an array.');

        $invalidKeys = array_diff(array_keys($value), $Mounter->registerKeys());
        if (!empty($invalidKeys)) {
            foreach ($invalidKeys as $invalidKey) {
                unset($value[$invalidKey]);
            }
        }

        return $value;
    }

    /**
     * 缓存挂载器的计算结果
     * @param array $value
     */
    private function setMountValueCache(array $value)
    {
        $this->_mountValueCache = array_merge($this->_mountValueCache, $value);
    }

    /**
     * 清除挂载缓存
     */
    private function clearMountValueCache()
    {
        $this->_mountValueCache = [];
    }

    //endregion
}