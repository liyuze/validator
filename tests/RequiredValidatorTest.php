<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\RequiredValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class RequiredValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\RequiredValidator
 */
class RequiredValidatorTest extends TestCase
{
    /**
     * 测试基本功能
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testType()
    {
        $validator = new RequiredValidator();
        $error = '';
        $this->assertTrue($validator->validate('', $error));
        $this->assertFalse($validator->validate(null, $error));
        $this->assertEquals('该输入的值不能为空。', $error);


        $parameters = new Parameters();
        $param_name = 'param_1';
        $parameters->config([
            $param_name => ['', 'required'],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, null);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不能为空。', $parameters->getFirstErrorMessage($param_name));
    }
}