<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\StringValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class StringValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\StringValidator
 */
class StringValidatorTest extends TestCase
{
    /**
     * 测试基本功能
     * @covers ::validate()
     * @covers ::validateParam()
     */
    public function testType()
    {
        $validator = new StringValidator([]);
        $error = '';
        $r = $validator->validate("1",$error);
        $this->assertTrue($r);
        $r = $validator->validate(1,$error);
        $this->assertTrue($r);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => ['1', 'string'],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 1);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));

    }

    /**
     * 测试字符串长度限定
     * @covers ::validate()
     * @covers ::validateParam()
     */
    public function testLength()
    {
        $validator = new StringValidator(['minLength' => 2, 'maxLength' => 4]);
        $error = '';
        $r = $validator->validate("333",$error);
        $this->assertTrue($r);
        $r = $validator->validate("1",$error);
        $this->assertFalse($r);
        $r = $validator->validate("55555",$error);
        $this->assertFalse($r);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => ["333", ['string', 'minLength' => 2, 'maxLength' => 4]],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, "1");
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, "55555");
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
    }

    /**
     * 测试严格验证模式
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testStrict()
    {
        $validator = new StringValidator(['strict' => true]);
        $error = '';
        $this->assertTrue($validator->validate('10', $error));
        $this->assertFalse($validator->validate(10, $error));
        $this->assertEquals('该输入的值必须是字符串。', $error);

        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => ['10', ['string', 'strict' => true]],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 10);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值必须是字符串。', $parameters->getFirstErrorMessage($param_name));
    }
}