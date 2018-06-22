<?php

namespace liyuze\validator\Validators;

use liyuze\validator\Parameters\Parameter;

class NumberValidator extends Validator
{
    /**
     * @var string 验证器名称
     */
    protected $_name = 'number';

    /**
     * @var bool 必须是整数
     */
    public $mustInt = false;

    /**
     * @var int|float 最小值.
     */
    public $min;

    /**
     * @var int|float 最大值.
     */
    public $max;

    /**
     * @var int|float 相等值
     */
    public $equal;

    /**
     * @var string 错误消息
     */
    public $message = '';
    public $messageMin = '';
    public $messageMax = '';
    public $messageEqual = '';

    /**
     * @var string 整数正则.
     */
    private $integerPattern = '/^\s*[+-]?\d+\s*$/';
    /**
     * @var string 数字正则.
     */
    private $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->message == '' && $this->message = $this->mustInt ? '{param_name}必须是整数。' : '{param_name}必须是数字。';
        $this->messageMin == '' && $this->messageMin = '{param_name}不能小于{min}。';
        $this->messageMax == '' && $this->messageMax = '{param_name}不能大于{max}。';
        $this->messageEqual == '' && $this->messageEqual = '{param_name}必须等于{equal}。';
    }

    /**
     * 验证参数对象
     * @param Parameter $parameter 参数
     * @return mixed
     */
    protected function _validateParam(Parameter $parameter)
    {
        $value = $parameter->getValue();

        //整数验证
        if ($this->mustInt) {
            if (!is_int($value) || !preg_match($this->integerPattern, $value)) {
                $this->addError($parameter, $this->message);
                return false;
            } else
                $value = intval($value);

        //浮点数验证
        } else {
            if (!is_numeric($value)) {
                $this->addError($parameter, $this->message);
                return false;
            } else
                $value = floatval($value);
        }

        //相等值验证
        if ($this->equal !== null && $value <> $this->equal) {
            $this->addError($parameter, $this->messageEqual, ['equal' => $this->equal]);
            return false;
        }

        //最大值验证
        if ($this->max !== null && $value > $this->max) {
            $this->addError($parameter, $this->messageMax, ['max' => $this->max], 'max');
        }

        //最小值验证
        if ($this->min !== null && $value < $this->min) {
            $this->addError($parameter, $this->messageMin, ['min' => $this->min], 'min');
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
        //整数验证
        if ($this->mustInt) {
            if (!is_int($value) || !preg_match($this->integerPattern, $value))
                return $this->message;
            else
                $value = intval($value);

            //浮点数验证
        } else {
            if (!is_numeric($value))
                return $this->message;
            else
                $value = floatval($value);
        }

        //相等值验证
        if ($this->equal !== null && $value <> $this->equal) {
            return [$this->messageEqual, ['equal' => $this->equal]];
        }

        //最大值验证
        if ($this->max !== null && $value > $this->max) {
            return [$this->messageMax, ['max' => $this->max]];
        }

        //最小值验证
        if ($this->min !== null && $value < $this->min) {
            return [$this->messageMax, ['min' => $this->min]];
        }

        return true;
    }
}
