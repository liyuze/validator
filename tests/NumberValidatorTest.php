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
     * @var null|Parameters
     */
    private $_parameters;

    public function setUp()
    {
        $this->_parameters = new Parameters();
        $this->_parameters->config([
            'param_1' => [10.2, ['number']],
            'param_2' => [10, ['integer']],
            //'param_2' => [10, ['number', 'mustInt' => true]],
            'param_3' => [10, ['integer', 'max' => 100, 'min' => 1]],
            'param_4' => [10, ['integer', 'equal' => 10]],
            'param_5' => [10, ['integer', 'strict' => true]],
        ], true);
    }

    /**
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

        $param_name = 'param_1';
        $parameters = $this->_parameters;
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

        $param_name = 'param_2';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 10.5);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值必须是整数。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
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

        $param_name = 'param_3';
        $parameters = $this->_parameters;
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

        $param_name = 'param_4';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 1);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值必须等于10。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
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

        $param_name = 'param_5';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, '10');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值必须是整数。', $parameters->getFirstErrorMessage($param_name));
    }
}