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
     * @var null|Parameters
     */
    private $_parameters;

    public function setUp()
    {
        $this->_parameters = new Parameters();
        $this->_parameters->config([
            'param_1' => [true, 'boolean'],
            'param_2' => [1, ['boolean', 'strict' => false]],
        ], true);
    }

    /**
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
        $this->assertEquals('该输入必须是 "true" 或 "false"。', $error);

        $param_name = 'param_1';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));

        $parameters->setParamsValue($param_name, false);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));

        $parameters->setParamsValue($param_name, 1);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals('param_1必须是 "true" 或 "false"。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testStrict()
    {
        $validator = new BooleanValidator(['strict' => false]);
        $error = '';
        $r = $validator->validate(1, $error);
        $this->assertTrue($r);

        $param_name = 'param_2';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
    }

}