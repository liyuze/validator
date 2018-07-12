<?php
namespace liyuze\validator\tests;

use liyuze\validator\Exceptions\Exception;
use liyuze\validator\Exceptions\InvalidConfigException;
use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\CallableValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class CallableValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\CallableValidator
 */
class CallableValidatorTest extends TestCase
{
    /**
     * 测试函数
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testFunction()
    {
        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => ['string', ['callable', 'method' => __NAMESPACE__.'\isString']],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 1);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals('输入的值不是一个字符串', $parameters->getFirstErrorMessage($param_name));

        $validator = new CallableValidator(['method' => __NAMESPACE__.'\isString2']);
        $error = '';
        $this->assertTrue($validator->validate('string', $error));
        $this->assertFalse($validator->validate(1, $error));
        $this->assertEquals('输入的值不是一个字符串', $error);
    }

    /**
     * 测试方法
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testMethod()
    {
        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [1, ['callable', 'method' => [$this, 'isInt']]],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 'error');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals('输入的值不是一个整数', $parameters->getFirstErrorMessage($param_name));

        $validator = new CallableValidator(['method' => [$this, 'isInt2']]);
        $error = '';
        $this->assertTrue($validator->validate(1, $error));
        $this->assertFalse($validator->validate('string', $error));
        $this->assertEquals('输入的值不是一个整数', $error);
    }

    /**
     * 测试静态方法
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testStaticMethod()
    {
        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [true, ['callable', 'method' => [self::class, 'isBoolean']]],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 'error');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals('输入的值不是一个布尔值', $parameters->getFirstErrorMessage($param_name));

        $validator = new CallableValidator(['method' => [self::class, 'isBoolean2']]);
        $error = '';
        $this->assertTrue($validator->validate(true, $error));
        $this->assertFalse($validator->validate('string', $error));
        $this->assertEquals('输入的值不是一个布尔值', $error);
    }

    /**
     * 测试匿名函数
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testAnonymousFunction()
    {
        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [function(){}, ['callable', 'method' => function ($value, $parameter, $methodValidator)
            {
                if (!($value instanceof \Closure)) {
                    $methodValidator->addError($parameter, '输入的值不是一个Closure');
                }
            }]],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 'error');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals('输入的值不是一个Closure', $parameters->getFirstErrorMessage($param_name));

        $validator = new CallableValidator(['method' => function ($value)
        {
            if (!($value instanceof \Closure)) {
                return '输入的值不是一个Closure';
            }

            return true;
        }]);
        $error = '';
        $this->assertTrue($validator->validate(function(){}, $error));
        $this->assertFalse($validator->validate('string', $error));
        $this->assertEquals('输入的值不是一个Closure', $error);
    }


    /**
     * 测试自定义参数
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testParam()
    {
        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [100, ['callable', 'method' => [self::class, 'eqValue'], 'param' => 100]],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 'error');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不等于100', $parameters->getFirstErrorMessage($param_name));

        $validator = new CallableValidator(['method' => [self::class, 'eqValue2'], 'param' => 100]);
        $error = '';
        $this->assertTrue($validator->validate(100, $error));
        $this->assertFalse($validator->validate('string', $error));
        $this->assertEquals('该输入的值不等于100', $error);
    }

    /**
     * 测试昵称
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testException()
    {
        try {
            $parameters = new Parameters();
            $parameters->config([
                'param_1' => ['string', ['callable']],
            ], true);
        } catch (\Exception $e) {
            $this->assertInstanceOf(InvalidConfigException::class, $e);
        }

        try {
            $validator = new CallableValidator();
        } catch (\Exception $e) {
            $this->assertInstanceOf(InvalidConfigException::class, $e);
        }

        try {
            $validator = new CallableValidator(['method' => '']);
        } catch (\Exception $e) {
            $this->assertInstanceOf(InvalidConfigException::class, $e);
        }
    }

    /**
     * @param $value
     * @param $Parameter
     * @param $Validator
     * @return boolean
     */
    public function isInt($value, $Parameter, $Validator)
    {
        if (!is_int($value)) {
            $Validator->addError($Parameter, '输入的值不是一个整数');
        }
        return true;
    }

    /**
     * @param $value
     * @param $Parameter
     * @param $Validator
     * @return boolean
     */
    public static function isBoolean($value, $Parameter, $Validator)
    {
        if (!is_bool($value)) {
            $Validator->addError($Parameter, '输入的值不是一个布尔值');
        }
        return true;
    }

    /**
     * @param $value
     * @param $Parameter
     * @param $Validator
     * @param $params
     * @return boolean
     */
    public static function eqValue($value, $Parameter, $Validator, $params)
    {
        if ($value != $params) {
            $Validator->addError($Parameter, '{param_name}的值不等于{value}', ['value' => $params]);
        }
        return true;
    }

    /**
     * @param $value
     * @return string|boolean
     */
    public function isInt2($value)
    {
        if (!is_int($value)) {
            return '输入的值不是一个整数';
        }
        return true;
    }

    /**
     * @param $value
     * @return string|boolean
     */
    public static function isBoolean2($value)
    {
        if (!is_bool($value)) {
            return '输入的值不是一个布尔值';
        }
        return true;
    }

    /**
     * @param $value
     * @param $params
     * @return boolean
     */
    public static function eqValue2($value, $params)
    {
        if ($value != $params) {
            return ['{param_name}的值不等于{value}', ['value' => $params]];
        }
        return true;
    }
}

function isString($value, $Parameter, $Validator)
{
    if (!is_string($value)) {
        $Validator->addError($Parameter, '输入的值不是一个字符串');
    }
}


function isString2($value)
{
    if (!is_string($value)) {
        return '输入的值不是一个字符串';
    }

    return true;
}
