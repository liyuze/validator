<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\IDCardValidator;
use liyuze\validator\Validators\MatchValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class IDCardValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\IDCardValidator
 */
class IDCardValidatorTest extends TestCase
{
    /**
     * @var null|Parameters
     */
    private $_parameters;

    public function setUp()
    {
        $this->_parameters = new Parameters();
        $this->_parameters->config([
            'param_1' => ['130423198908152222', ['id_card']],
            'param_2' => ['130423890815222', ['id_card', 'mustSecondGeneration' => false]],
            'param_3' => ['13042319890815222x', ['id_card', 'mustSecondGeneration' => false, 'min' => '19850101', 'max' => '19901231']],
            'param_4' => ['130423199508152222', ['id_card', 'minAge' => '18', 'maxAge' => '25']],
        ], true);
    }

    /**
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testType()
    {
        $validator = new IDCardValidator();
        $error = '';
        $this->assertTrue($validator->validate(130423198908152222, $error));
        $this->assertFalse($validator->validate(130423000008152222, $error));
        $this->assertEquals('该输入的值不是有效的身份证号。', $error);

        $param_name = 'param_1';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, '100000000008152222');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不是有效的身份证号。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testOneGeneration()
    {
        $validator = new IDCardValidator(['mustSecondGeneration' => false]);
        $error = '';
        $this->assertTrue($validator->validate(130423890815222, $error));
        $this->assertTrue($validator->validate(130423198908152222, $error));
        $this->assertFalse($validator->validate(15988888888, $error));
        $this->assertEquals('该输入的值不是有效的身份证号。', $error);

        $param_name = 'param_2';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 15988888888);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不是有效的身份证号。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testBirthday()
    {
        $validator = new IDCardValidator(['mustSecondGeneration' => false, 'min' => '19850101', 'max' => '19901231']);
        $error = '';
        $this->assertTrue($validator->validate(130423890815222, $error));
        $this->assertTrue($validator->validate(130423198908152222, $error));
        $this->assertFalse($validator->validate(130423840815222, $error));
        $this->assertEquals('该输入的值不可早于19850101。', $error);
        $this->assertFalse($validator->validate(130423199101012222, $error));
        $this->assertEquals('该输入的值不可晚于19901231。', $error);

        $param_name = 'param_3';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 130423890815222);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 130423910815222);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不可晚于19901231。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testAge()
    {
        $validator = new IDCardValidator(['minAge' => '18', 'maxAge' => '25']);
        $error = '';
        $this->assertTrue($validator->validate(130423199508152222, $error));
        $this->assertFalse($validator->validate(130423200208152222, $error));
        $this->assertEquals('该输入的值不可小于18周岁。', $error);
        $this->assertFalse($validator->validate(130423198908152222, $error));
        $this->assertEquals('该输入的值不可大于25周岁。', $error);

        $param_name = 'param_4';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 130423200508152222);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不可小于18周岁。', $parameters->getFirstErrorMessage($param_name));

        $parameters->setParamsValue($param_name, 130423198908152222);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不可大于25周岁。', $parameters->getFirstErrorMessage($param_name));
    }
}