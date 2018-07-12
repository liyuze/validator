<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\MatchValidator;
use liyuze\validator\Validators\NumberValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class NumberValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\NumberValidator
 */
class NumberValidatorTest extends TestCase
{
    /**
     * 测试基本功能
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testType()
    {
        $validator = new NumberValidator();
        $error = '';
        $this->assertTrue($validator->validate(10.2, $error));
        $this->assertTrue($validator->validate('10.2', $error));
        $this->assertFalse($validator->validate('10M', $error));
        $this->assertEquals('该输入的值必须是数字。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [10.2, ['number']],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, '10.2');
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, '10M');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值必须是数字。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * 测试 mustInt 属性，值必须是整数功能
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testInteger()
    {
        $validator = new NumberValidator(['mustInt' => true]);
        $error = '';
        $this->assertTrue($validator->validate(10, $error));
        $this->assertFalse($validator->validate(10.5, $error));
        $this->assertEquals('该输入的值必须是整数。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [10, ['integer']],
            //$param_name => [10, ['number', 'mustInt' => true]],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 10.5);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值必须是整数。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * 测试范围
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testMinAndMax()
    {
        $validator = new NumberValidator(['mustInt' => true, 'max' =>  100, 'min' => 1]);
        $error = '';
        $this->assertTrue($validator->validate(10, $error));
        $this->assertFalse($validator->validate(0, $error));
        $this->assertEquals('该输入的值不能小于1。', $error);
        $this->assertFalse($validator->validate(101, $error));
        $this->assertEquals('该输入的值不能大于100。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [10, ['integer', 'max' => 100, 'min' => 1]],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 0);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不能小于1。', $parameters->getFirstErrorMessage($param_name));
        $parameters->setParamsValue($param_name, 101);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不能大于100。', $parameters->getFirstErrorMessage($param_name));
    }


    /**
     * 测试相等
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testEqual()
    {
        $validator = new NumberValidator(['mustInt' => true, 'equal' =>  10]);
        $error = '';
        $this->assertTrue($validator->validate(10, $error));
        $this->assertFalse($validator->validate(1, $error));
        $this->assertEquals('该输入的值必须等于10。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [10, ['integer', 'equal' => 10]],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 1);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值必须等于10。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * 测试严格数据模式
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testStrict()
    {
        $validator = new NumberValidator(['mustInt' => true, 'strict' => true]);
        $error = '';
        $this->assertTrue($validator->validate(10, $error));
        $this->assertFalse($validator->validate('10', $error));
        $this->assertEquals('该输入的值必须是整数。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [10, ['integer', 'strict' => true]],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, '10');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值必须是整数。', $parameters->getFirstErrorMessage($param_name));
    }
}