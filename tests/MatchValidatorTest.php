<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\MatchValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class MatchValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\MatchValidator
 */
class MatchValidatorTest extends TestCase
{
    /**
     * 测试基本功能
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testType()
    {
        $validator = new MatchValidator(['pattern' => '/^\d{2}$/']);
        $error = '';
        $this->assertTrue($validator->validate(10, $error));
        $this->assertFalse($validator->validate(1000, $error));
        $this->assertEquals('该输入的值是无效的。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [10, ['match', 'pattern' => '/^\d{2}$/']],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 1000);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值是无效的。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * 测试 Not属性
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testNot()
    {
        $validator = new MatchValidator(['pattern' =>  '/^\d{2}$/', 'not' => true]);
        $error = '';
        $this->assertTrue($validator->validate(1000, $error));
        $this->assertFalse($validator->validate(10, $error));
        $this->assertEquals('该输入的值是无效的。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [1000, ['match', 'pattern' =>  '/^\d{2}$/', 'not' => true]],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 10);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值是无效的。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * 测试属性必填
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @expectedException \liyuze\validator\Exceptions\InvalidConfigException
     */
    public function testInvalidConfigException()
    {
        $validator = new MatchValidator();
    }


}