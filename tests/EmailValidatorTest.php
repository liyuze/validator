<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\EmailValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class EmailValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\EmailValidator
 */
class EmailValidatorTest extends TestCase
{
    /**
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testType()
    {
        $validator = new EmailValidator();
        $error = '';
        $this->assertTrue($validator->validate('290315384@qq.com', $error));
        $this->assertFalse($validator->validate('290315384qq.com', $error));
        $this->assertEquals('该输入的值不是有效的Email。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => ['290315384@qq.com', 'email'],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, '290315384qq.com');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不是有效的Email。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testAllowName()
    {
        $validator = new EmailValidator(['allowName' => true]);
        $error = '';
        $this->assertTrue($validator->validate('Liyuze <290315384@qq.com>', $error));
        $this->assertFalse($validator->validate('error data', $error));
        $this->assertEquals('该输入的值不是有效的Email。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => ['liyuze <290315384@qq.com>', ['email', 'allowName' => true]],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 'error data');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不是有效的Email。', $parameters->getFirstErrorMessage($param_name));
    }
}