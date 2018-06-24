<?php
namespace liyuze\validator\tests;

use liyuze\validator\Exceptions\InvalidConfigException;
use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\ArrayValidator;
use liyuze\validator\Validators\CompareValidator;
use liyuze\validator\Validators\StringValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class ArrayValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\CompareValidator
 */
class CompareValidatorTest extends TestCase
{
    /**
     * @var null|Parameters
     */
    private $_parameters;

    public function setUp()
    {
        $this->_parameters = new Parameters();
        $this->_parameters->config([
            'param_1' => [null, ['compare', 'skipIsEmpty' => false]],
            'param_2' => ['test value', ['compare', 'compareParamName' => 'param_3']],
            'param_3' => ['test value', ['compare', 'compareValue' => 'test value']],
            'param_4' => [5, ['compare', 'compareValue' => 3 ,'operator' => '>']],
        ], true);
    }

    public function testDefault()
    {
        $validator = new CompareValidator();
        $error = '';
        $this->assertTrue($validator->validate(null, $error));
        $this->assertFalse($validator->validate(1, $error));
        $this->assertEquals('该输入的值必须等于null。', $error);

        $param_name = 'param_1';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 'string');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals('param_1的值必须等于null。', $parameters->getFirstErrorMessage($param_name));
    }

    public function testCompareParam()
    {
        $param_name = 'param_2';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name,'error value');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals('param_2的值必须等于param_3。', $parameters->getFirstErrorMessage($param_name));
    }

    public function testCompareValue()
    {
        $validator = new CompareValidator(['compareValue' => 'string']);
        $error = '';
        $this->assertTrue($validator->validate('string', $error));
        $this->assertFalse($validator->validate('error data', $error));
        $this->assertEquals('该输入的值必须等于"string"。', $error);

        $param_name = 'param_3';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 'error data');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals('param_3的值必须等于"test value"。', $parameters->getFirstErrorMessage($param_name));
    }

    public function testOperator()
    {
        $validator = new CompareValidator(['compareValue' => 3, 'operator' => '>']);
        $error = '';
        $this->assertTrue($validator->validate(5, $error));
        $this->assertFalse($validator->validate(1, $error));
        $this->assertEquals('该输入的值必须大于3。', $error);

        $param_name = 'param_4';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 1);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals('param_4的值必须大于3。', $parameters->getFirstErrorMessage($param_name));
    }


    /**
     * @throws InvalidConfigException
     * @expectedException liyuze\validator\Exceptions\InvalidConfigException
     */
    public function testInvalidConfigException()
    {
        $validator = new CompareValidator(['operator' => 'error operator']);
    }
}