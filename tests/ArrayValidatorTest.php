<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\ArrayValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class ArrayValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\ArrayValidator
 */
class ArrayValidatorTest extends TestCase
{
    /**
     * 测试数组类型
     * @covers ::validate()
     * @covers ::validateParam()
     */
    public function testType()
    {
        $validator = new ArrayValidator();
        $error = '';
        $this->assertTrue($validator->validate([1,2,3], $error));
        $this->assertFalse($validator->validate(1, $error));
        $this->assertEquals('该输入必须是数组类型。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [[1], 'array'],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 'string');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'必须是数组类型。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * 测试数组key的验证配置
     * @covers ::validate()
     * @covers ::validateParam()
     */
    public function testKeyValidate()
    {
        $validator = new ArrayValidator(['keyValidateConfig' => 'integer']);
        $error = '';
        $this->assertTrue($validator->validate(['a','b'], $error));
        $this->assertFalse($validator->validate(['a' => 1, 'b' => 2], $error));
        $this->assertEquals('该输入的key值的格式不正确。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [['a', 'b'], ['array', 'keyValidateConfig' => 'integer']],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, ['a' => 1, 'b' => 2]);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的key值的格式不正确。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * 测试数组value的验证配置
     * @covers ::validate()
     * @covers ::validateParam()
     */
    public function testValueValidate()
    {
        $validator = new ArrayValidator(['valueValidateConfig' => 'string']);
        $error = '';
        $this->assertTrue($validator->validate(['a','b'], $error));
        $this->assertFalse($validator->validate(['a' => false, 'b' => false], $error));
        $this->assertEquals('该输入的value值的格式不正确。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [['a', 'b'], ['array', 'valueValidateConfig' => 'string']],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, ['a' => false, 'b' => false]);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的value值的格式不正确。', $parameters->getFirstErrorMessage($param_name));
    }
}