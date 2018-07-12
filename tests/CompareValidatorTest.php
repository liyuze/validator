<?php
namespace liyuze\validator\tests;

use liyuze\validator\Exceptions\InvalidConfigException;
use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\CompareValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class ArrayValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\CompareValidator
 */
class CompareValidatorTest extends TestCase
{
    /**
     * 测试null值对比
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testDefault()
    {
        $validator = new CompareValidator();
        $error = '';
        $this->assertTrue($validator->validate(null, $error));
        $this->assertFalse($validator->validate(1, $error));
        $this->assertEquals('该输入的值必须等于null。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [null, ['compare', 'skipIsEmpty' => false]],
        ]);
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 'string');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值必须等于null。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * 测试两个参数对比
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testCompareParam()
    {
        $parameters = new Parameters();
        $param_name = 'param_name';
        $param_name_2 = 'param_name_2';
        $parameters->config([
            $param_name => ['test value', ['compare', 'compareParamName' => $param_name_2]],
            $param_name_2 => ['test value'],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name,'error value');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值必须等于'.$param_name_2.'。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * 测试值对比
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testCompareValue()
    {
        $validator = new CompareValidator(['compareValue' => 'string']);
        $error = '';
        $this->assertTrue($validator->validate('string', $error));
        $this->assertFalse($validator->validate('error data', $error));
        $this->assertEquals('该输入的值必须等于"string"。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => ['test value', ['compare', 'compareValue' => 'test value']],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 'error data');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值必须等于"test value"。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * 测试对比操作者
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testOperator()
    {
        $validator = new CompareValidator(['compareValue' => 3, 'operator' => '>']);
        $error = '';
        $this->assertTrue($validator->validate(5, $error));
        $this->assertFalse($validator->validate(1, $error));
        $this->assertEquals('该输入的值必须大于3。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [5, ['compare', 'compareValue' => 3 ,'operator' => '>']],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 1);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值必须大于3。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * 测试对比操作符异常
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @expectedException \liyuze\validator\Exceptions\InvalidConfigException
     */
    public function testInvalidConfigException()
    {
        $validator = new CompareValidator(['operator' => 'error operator']);
    }
}