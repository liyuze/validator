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
     * @var null|Parameters
     */
    private $_parameters;

    public function setUp()
    {
        $this->_parameters = new Parameters();
        $this->_parameters->config([
            'param_1' => [3, ['in', 'range' => [3,4]]],
            'param_2' => ['3', ['in', 'range' => [3,4], 'strict' => false]],
        ], true);
    }

    /**
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

        $param_name = 'param_1';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, '3');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals('param_1的值是无效的。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testStrict()
    {
        $validator = new InValidator(['range' => [3,4], 'strict' => false]);
        $error = '';
        $this->assertTrue($validator->validate('3', $error));

        $param_name = 'param_2';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
    }

    /**
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @expectedException \liyuze\validator\Exceptions\InvalidConfigException
     */
    public function testInvalidConfigException()
    {
        $validator = new InValidator();
    }

    /**
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @expectedException \liyuze\validator\Exceptions\InvalidConfigException
     */
    public function testSetRange()
    {
        $validator = new InValidator(['range' => 'error data']);
    }

    /**
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @expectedException \liyuze\validator\Exceptions\InvalidConfigException
     */
    public function testSetRange2()
    {
        $validator = new InValidator(['range' => []]);
    }

}