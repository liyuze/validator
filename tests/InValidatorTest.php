<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\InValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class InValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\InValidator
 */
class InValidatorTest extends TestCase
{
    /**
     * 测试基本功能
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testType()
    {
        $validator = new InValidator(['range' => [3,4]]);
        $error = '';
        $this->assertTrue($validator->validate(3, $error));
        $this->assertFalse($validator->validate('3', $error));
        $this->assertEquals('该输入的值是无效的。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [3, ['in', 'range' => [3,4]]],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, '3');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值是无效的。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * 测试严格验证模式
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testStrict()
    {
        $validator = new InValidator(['range' => [3,4], 'strict' => false]);
        $error = '';
        $this->assertTrue($validator->validate('3', $error));


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => ['3', ['in', 'range' => [3,4], 'strict' => false]],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
    }

    /**
     * 测试 range 属性有效性
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @expectedException \liyuze\validator\Exceptions\InvalidConfigException
     */
    public function testInvalidConfigException()
    {
        $validator = new InValidator();
    }

    /**
     * 测试 range 属性有效性
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @expectedException \liyuze\validator\Exceptions\InvalidConfigException
     */
    public function testSetRange()
    {
        $validator = new InValidator(['range' => 'error data']);
    }

    /**
     * 测试 range 属性有效性
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @expectedException \liyuze\validator\Exceptions\InvalidConfigException
     */
    public function testSetRange2()
    {
        $validator = new InValidator(['range' => []]);
    }

}