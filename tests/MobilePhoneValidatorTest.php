<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\MobilePhoneValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class MobilePhoneValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\MobilePhoneValidator
 */
class MobilePhoneValidatorTest extends TestCase
{
    public function testPhone()
    {
        $validator = new MobilePhoneValidator(['type' => MobilePhoneValidator::TYPE_PHONE]);
        $error = '';
        $this->assertTrue($validator->validate(15901211111, $error));
        $this->assertFalse($validator->validate(10901211111, $error));
        $this->assertEquals('该输入的值不是有效的手机号码。', $error);
        $this->assertFalse($validator->validate('01012345678', $error));
        $this->assertEquals('该输入的值不是有效的手机号码。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => ['15901211111', ['phone']],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, '10901211111');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不是有效的手机号码。', $parameters->getFirstErrorMessage($param_name));
        $parameters->setParamsValue($param_name, '01012345678');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不是有效的手机号码。', $parameters->getFirstErrorMessage($param_name));
    }

    public function testMobile()
    {
        $validator = new MobilePhoneValidator(['type' => MobilePhoneValidator::TYPE_MOBILE]);
        $error = '';
        $this->assertTrue($validator->validate('01012345678', $error));
        $this->assertTrue($validator->validate('12345678', $error));
        $this->assertFalse($validator->validate(15901211111, $error));
        $this->assertEquals('该输入的值不是有效的电话号码。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => ['01012345678', ['mobile']],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, '15901211111');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不是有效的电话号码。', $parameters->getFirstErrorMessage($param_name));
    }

    public function testMobileOrPhone()
    {
        $validator = new MobilePhoneValidator();
        $error = '';
        $this->assertTrue($validator->validate('01012345678', $error));
        $this->assertTrue($validator->validate('15901211111', $error));
        $this->assertFalse($validator->validate(555, $error));
        $this->assertEquals('该输入的值不是有效的手机号码或电话号码。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => ['01012345678', ['mobile_phone']],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, '15901211111');
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, '555');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不是有效的手机号码或电话号码。', $parameters->getFirstErrorMessage($param_name));
    }
}