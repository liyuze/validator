<?php

namespace liyuze\validator\Validators;

use liyuze\validator\Exceptions\InvalidConfigException;
use liyuze\validator\Parameters\Parameter;

class MobilePhoneValidator extends Validator
{
    const TYPE_MOBILE_PHONE     = 'mobile_phone';
    const TYPE_MOBILE           = 'mobile';
    const TYPE_PHONE            = 'phone';

    /**
     * @var string 验证器名称
     */
    protected $_name = 'mobile_phone';

    /**
     * @var string 验证类型
     */
    public $type = self::TYPE_MOBILE_PHONE;

    public $international = false;

    /**
     * @var string 正则表达式
     */
    public $patternPhone = '/^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\d{8}$/';

    /**
     * @var string 正则表达式
     */
    public $patternMobile = '/^((0\d{2,3})-?)?(\d{7,8})(-(\d{3,}))?$/';

    /**
     * @var string 错误消息
     */
    public $message = '';


    /**
     * InValidator constructor.
     * @param $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->message == '' && $this->message = $this->_getMessage();
    }

    /**
     * 验证参数对象
     * @param Parameter $parameter 参数
     * @return mixed
     */
    protected function _validateParam(Parameter $parameter)
    {
        $value = $parameter->getValue();

        if ($this->type == self::TYPE_PHONE && !preg_match($this->patternPhone, $value))
            $this->addError($parameter, $this->message);

        if ($this->type == self::TYPE_MOBILE && !preg_match($this->patternMobile, $value))
            $this->addError($parameter, $this->message);

        if ($this->type == self::TYPE_MOBILE_PHONE && !preg_match($this->patternPhone, $value) && !preg_match($this->patternMobile, $value))
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
        if ($this->type == self::TYPE_PHONE && !preg_match($this->patternPhone, $value))
            return $this->message;

        if ($this->type == self::TYPE_MOBILE && !preg_match($this->patternMobile, $value))
            return $this->message;

        if ($this->type == self::TYPE_MOBILE_PHONE && !preg_match($this->patternPhone, $value) && !preg_match($this->patternMobile, $value))
            return $this->message;

        return true;
    }

    /**
     * 获取错误消息
     * @return string
     */
    private function _getMessage()
    {
        switch ($this->type) {
            case self::TYPE_PHONE:
                $message = '{param_name}的值不是有效的手机号码。';
                break;
            case self::TYPE_MOBILE:
                $message = '{param_name}的值不是有效的电话号码。';
                break;
            default:        //self::TYPE_MOBILE_PHONE
                $message = '{param_name}的值不是有效的手机号码或电话号码。';
        }

        return $message;
    }
}
