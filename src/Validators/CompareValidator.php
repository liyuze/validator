<?php

namespace liyuze\validator\Validators;

use liyuze\validator\Exceptions\Exception;
use liyuze\validator\Parameters\Parameter;

class CompareValidator extends Validator
{
    /**
     * @var string 验证器名称
     */
    protected $_name = 'compare';

    /**
     * @var string 需要对比的参数的name值
     */
    public $compareParamName;

    /**
     * @var mixed 需要对比的值
     */
    public $compareValue;

    /**
     * @var string 对比操作符
     * ==：检查两值是否相等。比对为非严格模式。
     * ===：检查两值是否全等。比对为严格模式。
     * !=：检查两值是否不等。比对为非严格模式。
     * !==：检查两值是否不全等。比对为严格模式。
     * >：检查待测目标值是否大于给定被测值。
     * >=：检查待测目标值是否大于等于给定被测值。
     * <：检查待测目标值是否小于给定被测值。
     * <=：检查待测目标值是否小于等于给定被测值。
     */
    public $operator = '==';

    private $_validOperator = [
        '==', '===', '!=', '!==', '>', '>=', '<', '<='
    ];

    /**
     * @var bool 去除字符串两边的空格
     */
    public $trimString = true;

    /**
     * @var string 错误消息
     */
    public $message = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        if (!in_array($this->operator, $this->_validOperator))
            throw new Exception();//todo 无效的参数

        $this->message == '' && $this->message = $this->compareMessage($this->operator);
    }

    /**
     * 验证参数对象
     * @param Parameter $parameter 参数
     * @return bool
     */
    protected function _validateParam(Parameter $parameter)
    {
        $value = $parameter->getValue();

        //参数对比
        if ($this->compareParamName !== null) {

            $otherParameter = $parameter->getParameters()->getParam($this->compareParamName);
            if ($otherParameter === null) {
                //todo 参数无效，没有该参数
            }
            if (!$this->compareValue($this->operator, $value, $otherParameter->getValue())) {
                $this->addError($parameter, $this->message, ['value_or_param_name' => $otherParameter->getAlias()]);
            }
            //值对比
        } else {
            if (!$this->compareValue($this->operator, $value, $this->compareValue)) {
                $this->addError($parameter, $this->message, ['value_or_param_name' => $this->compareParamName]);
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
        if (!$this->compareValue($this->operator, $value, $this->compareValue)) {
            return [$this->message, ['value_or_param_name' => $this->compareValue]];
        }

        return true;
    }

    /**
     * 对比值
     * @param string $operator 对比符
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    private function compareValue($operator, $value1, $value2)
    {
        //去除字符串两边空格
        is_string($value1) && $this->trimString && $value1 = trim($value1);
        is_string($value2) && $this->trimString && $value2 = trim($value2);

        switch ($operator) {
            case '==':
                return $value1 == $value2;
            case '===':
                return $value1 === $value2;
            case '!=':
                return $value1 != $value2;
            case '!==':
                return $value1 !== $value2;
            case '>':
                return $value1 > $value2;
            case '>=':
                return $value1 >= $value2;
            case '<':
                return $value1 < $value2;
            case '<=':
                return $value1 <= $value2;
            default:
                return false;
        }
    }

    /**
     * 对比错误消息
     * @param string $operator 对比符
     * @return bool
     */
    private function compareMessage($operator)
    {
        switch ($operator) {
            case '==':
                return '{param_name}必须等于{value_or_param_name}';
            case '===':
                return '{param_name}必须等于{value_or_param_name}';
            case '!=':
                return '{param_name}不能等于{value_or_param_name}';
            case '!==':
                return '{param_name}不能等于{value_or_param_name}';
            case '>':
                return '{param_name}必须大于{value_or_param_name}';
            case '>=':
                return '{param_name}不能小于{value_or_param_name}';
            case '<':
                return '{param_name}必须小于{value_or_param_name}';
            case '<=':
                return '{param_name}不能大于{value_or_param_name}';
            default:
                return '';
        }
    }
}
