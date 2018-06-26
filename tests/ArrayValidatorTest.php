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
     * @var null|Parameters
     */
    private $_parameters;

    public function setUp()
    {
        $this->_parameters = new Parameters();
        $this->_parameters->config([
            'param_1' => [[1], 'array'],
            'param_2' => [['a', 'b'], ['array', 'keyValidateConfig' => 'integer']],
            'param_3' => [['a', 'b'], ['array', 'valueValidateConfig' => 'string']],
        ], true);
    }

    /**
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

        $param_name = 'param_1';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 'string');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals('param_1必须是数组类型。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
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

        $param_name = 'param_2';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, ['a' => 1, 'b' => 2]);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals('param_2的key值的格式不正确。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
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

        $param_name = 'param_3';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, ['a' => false, 'b' => false]);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals('param_3的value值的格式不正确。', $parameters->getFirstErrorMessage($param_name));
    }
}