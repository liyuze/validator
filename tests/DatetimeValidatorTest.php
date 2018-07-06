<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\DateTimeValidator;
use liyuze\validator\Validators\EmailValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class DatetimeValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\DateTimeValidator
 */
class DatetimeValidatorTest extends TestCase
{
    /**
     * @var null|Parameters
     */
    private $_parameters;

    public function setUp()
    {
        $this->_parameters = new Parameters();
        $this->_parameters->config([
            'param_1' => ['2018-07-06 12:12:12', ['datetime', 'format' => 'php:Y-m-d H:i:s',
                            'min' => '2018-07-06 00:00:00', 'max' => '2018-07-06 23:59:59']],
            'param_2' => ['2018-07-06', ['date', 'format' => 'php:Y-m-d',
                            'min' => '2018-07-01', 'max' => '2018-07-31']],
            'param_3' => ['12:00:00', ['time', 'format' => 'php:H:i:s',
                            'min' => '06:00:00', 'max' => '18:00:00']],
            'param_4' => ['2018-07-06', ['date', 'format' => 'yyyy-MM-dd']],
        ], true);
    }

    /**
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testDateTime()
    {
        $param_name = 'param_1';
        $min = '2018-07-06 00:00:00';
        $max = '2018-07-06 23:59:59';
        $testValue = '2018-07-06';
        $testMinValue = '2018-07-05 12:12:12';
        $testMaxValue = '2018-07-08 12:12:12';

        $validator = new DateTimeValidator(['format' => 'php:Y-m-d H:i:s', 'min' => $min, 'max' => $max]);
        $error = '';
        $this->assertTrue($validator->validate('2018-07-06 12:12:12', $error));
        $this->assertFalse($validator->validate($testValue, $error));
        $this->assertEquals('该输入的值不是有效的时间值。', $error);
        $this->assertFalse($validator->validate($testMinValue, $error));
        $this->assertEquals('该输入的值不能早于'.$min.'。', $error);
        $this->assertFalse($validator->validate($testMaxValue, $error));
        $this->assertEquals('该输入的值不能晚于'.$max.'。', $error);

        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, $testValue);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不是有效的时间值。', $parameters->getFirstErrorMessage($param_name));
        $parameters->setParamsValue($param_name, $testMinValue);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不能早于'.$min.'。', $parameters->getFirstErrorMessage($param_name));
        $parameters->setParamsValue($param_name, $testMaxValue);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不能晚于'.$max.'。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testDate()
    {
        $param_name = 'param_2';
        $min = '2018-07-01';
        $max = '2018-07-31';
        $testValue = '20180706';
        $testMinValue = '2018-06-05';
        $testMaxValue = '2018-08-08';

        $validator = new DateTimeValidator(['format' => 'php:Y-m-d', 'min' => $min, 'max' => $max]);
        $error = '';
        $this->assertTrue($validator->validate('2018-07-06', $error));
        $this->assertFalse($validator->validate($testValue, $error));
        $this->assertEquals('该输入的值不是有效的时间值。', $error);
        $this->assertFalse($validator->validate($testMinValue, $error));
        $this->assertEquals('该输入的值不能早于'.$min.'。', $error);
        $this->assertFalse($validator->validate($testMaxValue, $error));
        $this->assertEquals('该输入的值不能晚于'.$max.'。', $error);

        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, $testValue);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不是有效的时间值。', $parameters->getFirstErrorMessage($param_name));
        $parameters->setParamsValue($param_name, $testMinValue);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不能早于'.$min.'。', $parameters->getFirstErrorMessage($param_name));
        $parameters->setParamsValue($param_name, $testMaxValue);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不能晚于'.$max.'。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testTime()
    {

        $param_name = 'param_3';
        $min = '06:00:00';
        $max = '18:00:00';
        $testValue = '20180706';
        $testMinValue = '01:30:30';
        $testMaxValue = '18:00:01';

        $validator = new DateTimeValidator(['format' => 'php:H:i:s', 'min' => $min, 'max' => $max]);
        $error = '';
        $this->assertTrue($validator->validate('12:00:00', $error));
        $this->assertFalse($validator->validate($testValue, $error));
        $this->assertEquals('该输入的值不是有效的时间值。', $error);
        $this->assertFalse($validator->validate($testMinValue, $error));
        $this->assertEquals('该输入的值不能早于'.$min.'。', $error);
        $this->assertFalse($validator->validate($testMaxValue, $error));
        $this->assertEquals('该输入的值不能晚于'.$max.'。', $error);

        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, $testValue);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不是有效的时间值。', $parameters->getFirstErrorMessage($param_name));
        $parameters->setParamsValue($param_name, $testMinValue);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不能早于'.$min.'。', $parameters->getFirstErrorMessage($param_name));
        $parameters->setParamsValue($param_name, $testMaxValue);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不能晚于'.$max.'。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testIcu()
    {

        $param_name = 'param_4';
        $testValue = '20180706';

        $validator = new DateTimeValidator(['format' => 'yyyy-MM-dd']);
        $error = '';
        $this->assertTrue($validator->validate('2018-07-06', $error));
        $this->assertFalse($validator->validate($testValue, $error));
        $this->assertEquals('该输入的值不是有效的时间值。', $error);

        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, $testValue);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不是有效的时间值。', $parameters->getFirstErrorMessage($param_name));
    }

}