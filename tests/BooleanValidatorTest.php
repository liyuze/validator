<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\BooleanValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class BooleanValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\BooleanValidator
 */
class BooleanValidatorTest extends TestCase
{
    /**
     * 测试基本功能
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testType()
    {
        $validator = new BooleanValidator();
        $error = '';
        $this->assertTrue($validator->validate(true, $error));
        $this->assertTrue($validator->validate(false, $error));
        $this->assertFalse($validator->validate(1, $error));
        $this->assertEquals('该输入的值必须是 "true" 或 "false"。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [true, 'boolean'],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));

        $parameters->setParamsValue($param_name, false);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));

        $parameters->setParamsValue($param_name, 1);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值必须是 "true" 或 "false"。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * 测试严格验证
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testStrict()
    {
        $validator = new BooleanValidator(['strict' => false]);
        $error = '';
        $r = $validator->validate(1, $error);
        $this->assertTrue($r);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [1, ['boolean', 'strict' => false]],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
    }

}