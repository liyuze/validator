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
     * @var null|Parameters
     */
    private $parameters;

    public function setUp()
    {
        $this->parameters = new Parameters();
        $this->parameters->config([
            'param_1' => ['1', 'string'],
            'param_2' => ["55555", ['string', 'minLength' => 5]],
            'param_3' => ["55555", ['string', 'maxLength' => 5]],
        ], true);
    }

    /**
     * @covers ::validate()
     * @covers ::validateParam()
     */
    public function testType()
    {
        $validator = new StringValidator([]);
        $error = '';
        $r = $validator->validate("1",$error);
        $this->assertEquals($r, true);
        $r = $validator->validate(1,$error);
        $this->assertEquals($r, false);

        $param_name = 'param_1';
        $parameters = $this->parameters;
        $parameters->validate();
        $this->assertEquals($parameters->hasError($param_name), false);
        $parameters->setParamsValue($param_name, 1);
        $parameters->validate();
        $this->assertEquals($parameters->hasError($param_name), true);

    }

    /**
     * @covers ::validate()
     * @covers ::validateParam()
     */
    public function testMinLength()
    {
        $validator = new StringValidator(['minLength' => 5]);
        $error = '';
        $r = $validator->validate("555555",$error);
        $this->assertEquals($r, true);
        $r = $validator->validate("4444",$error);
        $this->assertEquals($r, false);

        $param_name = 'param_2';
        $parameters = $this->parameters;
        $parameters->validate();
        $this->assertEquals($parameters->hasError($param_name), false);
        $parameters->setParamsValue($param_name, "4444");
        $parameters->validate();
        $this->assertEquals($parameters->hasError($param_name), true);
    }

    /**
     * @covers ::validate()
     * @covers ::validateParam()
     */
    public function testMaxLength()
    {
        $validator = new StringValidator(['maxLength' => 4]);
        $error = '';
        $r = $validator->validate("4444",$error);
        $this->assertEquals($r, true);
        $r = $validator->validate("555555",$error);
        $this->assertEquals($r, false);


        $param_name = 'param_3';
        $parameters = $this->parameters;
        $parameters->validate();
        $this->assertEquals($parameters->hasError($param_name), false);
        $parameters->setParamsValue($param_name, "666666");
        $parameters->validate();
        $this->assertEquals($parameters->hasError($param_name), true);
    }
}