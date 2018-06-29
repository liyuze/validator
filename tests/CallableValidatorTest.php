<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameters;
use PHPUnit\Framework\TestCase;

/**
 * Class CallableValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\CallableValidator
 */
class CallableValidatorTest extends TestCase
{
    /**
     * @var null|Parameters
     */
    private $_parameters;

    public function setUp()
    {
        $this->_parameters = new Parameters();
        $this->_parameters->config([
            'param_1' => ['string', ['callable', 'method' => __NAMESPACE__.'\isString']],
            'param_2' => [1, ['callable', 'method' => 'isInt', 'target' => $this]],
            'param_3' => [true, ['callable', 'method' => 'isBoolean', 'target' => $this]],
            'param_4' => [function(){}, ['callable', 'method' => function ($value, $parameter, $methodValidator)
            {
                if (!($value instanceof \Closure)) {
                    $methodValidator->addError($parameter, '输入的值不是一个Closure');
                }
            }]],
        ], true);
    }

    /**
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testFunction()
    {

        function isString($value, $parameter, $methodValidator)
        {
            if (!is_string($value)) {
                $methodValidator->addError($parameter, '输入的值不是一个字符串');
            }
        }

        $param_name = 'param_1';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 1);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals('输入的值不是一个字符串', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testMethod()
    {

        $param_name = 'param_2';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 'error');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals('输入的值不是一个整数', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testStaticMethod()
    {
        $param_name = 'param_3';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 'error');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals('输入的值不是一个布尔值', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testAnonymousFunction()
    {
        $param_name = 'param_4';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 'error');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals('输入的值不是一个Closure', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * @param $value
     * @param $parameter
     * @param $methodValidator
     */
    public function isInt($value, $parameter, $methodValidator)
    {
        if (!is_int($value)) {
            $methodValidator->addError($parameter, '输入的值不是一个整数');
        }
    }

    /**
     * @param $value
     * @param $parameter
     * @param $methodValidator
     */
    public static function isBoolean($value, $parameter, $methodValidator)
    {
        if (!is_bool($value)) {
            $methodValidator->addError($parameter, '输入的值不是一个布尔值');
        }
    }

}