<?php

namespace liyuze\validator\Validators;

use liyuze\validator\Exceptions\InvalidConfigException;
use liyuze\validator\Parameters\Parameter;

class IDCardValidator extends Validator
{
    /**
     * @var string 验证器名称
     */
    protected $_name = 'id_card';

    /**
     * @var string 正则表达式
     */
    public $pattern_18 = '/^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/';
    public $pattern_15 = '/^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}$/';

    /**
     * @var bool 必须是第二代身份证，为 false 时第一代身份证将验证通过，默认是 true 。
     */
    public $mustSecondGeneration = true;

    /**
     * @var string 最小出生日期，可以被 [strtotime()]处理的日期字符串。
     */
    public $min;

    /**
     * @var string 最大出生日期，可以被 [strtotime()]处理的日期字符串。
     */
    public $max;

    /**
     * @var integer 最小周岁。
     */
    public $minAge;

    /**
     * @var integer 最大周岁。
     */
    public $maxAge;

    /**
     * @var string 错误消息
     */
    public $message = '';
    public $messageMin = '';
    public $messageMax = '';
    public $messageMinAge = '';
    public $messageMaxAge = '';

    /**
     * InValidator constructor.
     * @param $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->message == '' && $this->message = '{param_name}的值不是有效的身份证号。';
        $this->messageMin == '' && $this->messageMin = '{param_name}的值不可早于{value}。';
        $this->messageMax == '' && $this->messageMax = '{param_name}的值不可晚于{value}。';
        $this->messageMinAge == '' && $this->messageMinAge = '{param_name}的值不可小于{age}周岁。';
        $this->messageMaxAge == '' && $this->messageMaxAge = '{param_name}的值不可大于{age}周岁。';
    }

    /**
     * 验证参数对象
     * @param Parameter $parameter 参数
     * @return mixed
     */
    protected function _validateParam(Parameter $parameter)
    {
        $value = $parameter->getValue();

        //身份证号校验
        if (!(preg_match($this->pattern_18, $value) ||
            (!$this->mustSecondGeneration && preg_match($this->pattern_15, $value)))) {
            $this->addError($parameter, $this->message);
            return false;
        }

        //出生日期
        $birthTime = self::getBirthTimeFromIDCard($value);
        $now = time();

        //出生日期校验
        if ($this->min !== null && $birthTime < strtotime($this->min))
            $this->addError($parameter, $this->messageMin, ['value' => $this->min]);
        elseif ($this->max !== null && $birthTime > strtotime($this->max))
            $this->addError($parameter, $this->messageMax, ['value' => $this->max]);

        //年龄验证
        if ($this->minAge !== null && $now < strtotime($this->minAge. 'years', $birthTime))
            $this->addError($parameter, $this->messageMinAge, ['age' => $this->minAge]);
        elseif ($this->maxAge !== null && $now > strtotime($this->maxAge. 'years', $birthTime))
            $this->addError($parameter, $this->messageMaxAge, ['age' => $this->maxAge]);

        return true;
    }

    /**
     * 验证值
     * @param mixed $value 值
     * @return mixed
     */
    protected function _validateValue($value)
    {
        //身份证号校验
        if (!(preg_match($this->pattern_18, $value) ||
            (!$this->mustSecondGeneration && preg_match($this->pattern_15, $value))))
            return $this->message;

        //出生日期
        $birthTime = self::getBirthTimeFromIDCard($value);
        $now = time();

        //出生日期校验
        if ($this->min !== null && $birthTime < strtotime($this->min))
            return [$this->messageMin, ['value' => $this->min]];
        elseif ($this->max !== null && $birthTime > strtotime($this->max))
            return [$this->messageMax, ['value' => $this->max]];

        //年龄验证
        if ($this->minAge !== null && $now < strtotime($this->minAge. 'years', $birthTime))
            return [$this->messageMinAge, ['age' => $this->minAge]];
        elseif ($this->maxAge !== null && $now > strtotime($this->maxAge. 'years', $birthTime))
            return [$this->messageMaxAge, ['age' => $this->maxAge]];

        return true;
    }

    /**
     * @param string $value 身份证号
     * @return false|int 出生秒数
     */
    private static function getBirthTimeFromIDCard($value)
    {
        if (strlen($value) == 18) {
            $birthday = substr($value, 6, 8);
        } else {
            $birthday = '19'.substr($value, 6, 6);
        }
        return strtotime($birthday);
    }
}
