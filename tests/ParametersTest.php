<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameter;
use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Class ParametersTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Parameters\Parameters
 */
class ParametersTest extends TestCase
{
    use ReflectionTrait;

    //region 参数相关测试

    /**
     * @covers ::__construct
     * @covers ::hasParam()
     * @return Parameters
     */
    public function testConstruct()
    {
        $param_1 = 1;
        $ps = new Parameters([
            'param_1' => $param_1,
        ]);
        $this->assertEquals($ps->hasParam('param_1'), true);

        return $ps;
    }


    /**
     * @param Parameters $ps
     * @covers ::getParam()
     * @covers ::__get()
     * @depends clone testConstruct
     */
    public function testGetParam(Parameters $ps)
    {
        $p1 = $ps->getParam('param_1');
        $this->assertInstanceOf(Parameter::class, $p1);

        $p1 = $ps->param_1;
        $this->assertInstanceOf(Parameter::class, $p1);

        $p2 = $ps->getParam('param_2');
        $this->assertEquals(null, $p2);
    }

    /**
     * @param Parameters $ps
     * @covers ::getParamValue()
     * @depends clone testConstruct
     */
    public function testGetParamValue(Parameters $ps)
    {
        $value = $ps->getParamValue('param_1');
        $this->assertEquals(1, $value);

        $value = $ps->getParamValue('param_2');
        $this->assertEquals(null, $value);
    }

    /**
     * @param Parameters $ps
     * @covers ::setParamsValue()
     * @depends clone testConstruct
     */
    public function testSetParamsValue(Parameters $ps)
    {
        $ps->addValidator('param_1', 'string');
        $ps->validate();
        $this->assertFalse($ps->hasError('param_1'));
        $param_1 = $ps->getParam('param_1');

        $validator = $this->getPrivateProperty($param_1, '_validators');
        $validateStatus = $this->getPrivateProperty($validator[0], '_validateStatus');
        $this->assertEquals(Validator::VALIDATE_STATUS_DONE, $validateStatus);

        $ps->setParamsValue('param_1', 11);
        $this->assertFalse($ps->hasError('param_1'), '清除验证错误消息');

        $param_1 = $ps->getParam('param_1');
        $validator = $this->getPrivateProperty($param_1, '_validators');
        $validateStatus = $this->getPrivateProperty($validator[0], '_validateStatus');
        $this->assertEquals(Validator::VALIDATE_STATUS_WAITING, $validateStatus);
    }

    /**
     * @param Parameters $ps
     * @covers ::addParam()
     * @depends clone testConstruct
     * @return Parameters
     */
    public function testAddParam(Parameters $ps)
    {
        $param_2 = 2;
        $ps->addParam('param_2', $param_2);
        $this->assertTrue($ps->hasParam('param_2'));

        return $ps;
    }

    /**
     * @param Parameters $ps
     * @covers ::getParamsValue()
     * @depends clone testAddParam
     */
    public function testGetParamsValue(Parameters $ps)
    {
        $value = $ps->getParamsValue(['param_2']);
        $this->assertArraySubset($value, ['param_2' => 2]);

        $value = $ps->getParamsValue([]);
        $this->assertArraySubset($value, ['param_1' => 1, 'param_2' => 2]);
    }

    /**
     * @param Parameters $ps
     * @covers ::addParams()
     * @depends clone testConstruct
     */
    public function testAddParams(Parameters $ps)
    {
        $param_3 = 3;
        $param_4 = 4;
        $ps->addParams([
            'param_3' => $param_3,
            'param_4' => $param_4
        ]);
        $this->assertTrue($ps->hasParam('param_3'));
        $this->assertTrue($ps->hasParam('param_4'));
    }

    /**
     * @param Parameters $ps
     * @covers ::clearParams()
     * @depends clone testConstruct
     */
    public function testClearParams(Parameters $ps)
    {
        $ps->clearParams();
        $this->assertFalse($ps->hasParam('param_1'));
    }
    //endregion

    //region 错误消息相关test

    /**
     * @param Parameters $ps
     * @covers ::addError()
     * @depends clone testConstruct
     * @return Parameters
     */
    public function testAddError(Parameters $ps)
    {
        $ps->addError('param_1', 'string', '{param_name} error message {a} and {b}',
            ['param_name' => 'param_1', 'a' => 'A', 'b' => 'B']);

        $ps->addError('param_1', 'number', '{param_name} error message',
            ['param_name' => 'param_1']);

        $ps->addError('param_2', 'number', '{param_name} error message',
            ['param_name' => 'param_2']);

        return $ps;
    }

    /**
     * @param Parameters $ps
     * @covers ::hasError()
     * @depends clone testAddError
     */
    public function testHasError(Parameters $ps)
    {
        $hasError = $ps->hasError();
        $this->assertTrue($hasError);

        $hasError = $ps->hasError('param_1');
        $this->assertTrue($hasError);

        $hasError = $ps->hasError('param_9');
        $this->assertFalse($hasError);
    }

    /**
     * @param Parameters $ps
     * @covers ::getErrors()
     * @depends clone testAddError
     */
    public function testGetErrors(Parameters $ps)
    {
        $errors = $ps->getErrors();
        $this->assertEquals([
            'param_1' => [
                'string' => 'param_1 error message A and B',
                'number' => 'param_1 error message',
            ],
            'param_2' => [
                'number' => 'param_2 error message',
            ]
        ], $errors);

        $errors = $ps->getErrors('param_2');
        $this->assertEquals([
            'number' => 'param_2 error message',
        ], $errors);
    }

    /**
     * @param Parameters $ps
     * @covers ::getFirstErrorMessage()
     * @depends clone testAddError
     */
    public function testGetFirstErrorMessage(Parameters $ps)
    {
        $error = $ps->getFirstErrorMessage();
        $this->assertContains($error, ['param_1 error message A and B', 'param_1 error message']);

        $error = $ps->getFirstErrorMessage('param_2');
        $this->assertContains($error, ['param_2 error message']);

        $error = $ps->getFirstErrorMessage('param_9');
        $this->assertEquals('', $error);
    }

    /**
     * @param Parameters $ps
     * @covers ::clearErrors()
     * @depends clone testAddError
     */
    public function testClearErrors(Parameters $ps)
    {
        $ps->clearErrors('param_1');
        $hasErrors = $ps->hasError('param_1');
        $this->assertFalse($hasErrors);

        $hasErrors = $ps->hasError();
        $this->assertTrue($hasErrors);
        $ps->clearErrors();
        $hasErrors = $ps->hasError();
        $this->assertFalse($hasErrors);
    }

    //endregion

    //region 验证器配置相关test

    /**
     * @param Parameters $ps
     * @covers ::config()
     * @depends clone testConstruct
     */
    public function testConfig(Parameters $ps)
    {
        $param_2 = 2;
        $param_3 = 3;
        $ps->config([
            'param_2' => [$param_2, 'string'],
            'param_3' => [$param_3]
        ], true);

        $hasParam = $ps->hasParam('param_3');
        $this->assertTrue($hasParam);

        $validateAllParams = $ps->validateAllParams;
        $this->assertTrue($validateAllParams);
    }

    /**
     * @param Parameters $ps
     * @covers ::parseValidatorConfig()
     * @depends clone testConstruct
     */
    public function testParseValidatorConfig(Parameters $ps)
    {
        $config = 'string';
        $parseConfig = $this->callPrivateMethod($ps, 'parseValidatorConfig', [$config]);
        $this->assertSame([['string']], $parseConfig);

        $config = ['string'];
        $parseConfig = $this->callPrivateMethod($ps, 'parseValidatorConfig', [$config]);
        $this->assertSame([['string']], $parseConfig);

        $config = ['string', 'maxLength' => 150, 'number', 'mustInt' => true];
        $parseConfig = $this->callPrivateMethod($ps, 'parseValidatorConfig', [$config]);
        $this->assertSame([['string', 'maxLength' => 150], ['number', 'mustInt' => true]], $parseConfig);

        $config = [['string', 'maxLength' => 150], ['number', 'mustInt' => true]];
        $parseConfig = $this->callPrivateMethod($ps, 'parseValidatorConfig', [$config]);
        $this->assertSame([['string', 'maxLength' => 150], ['number', 'mustInt' => true]], $parseConfig);

        $config = 'string|maxLength=150|number|mustInt=1';
        $parseConfig = $this->callPrivateMethod($ps, 'parseValidatorConfig', [$config]);
        $this->assertSame([['string', 'maxLength' => '150'], ['number', 'mustInt' => '1']], $parseConfig);
    }

    /**
     * @param Parameters $ps
     * @param Parameters $ps2
     * @covers ::addValidator()
     * @covers ::setValidatorConfig()
     * @covers ::validate()
     * @depends clone testConstruct
     * @depends clone testAddParam
     */
    public function testValidate(Parameters $ps, Parameters $ps2)
    {

        $ps->addValidator('param_1', 'boolean');
        $ps->validate();
        $this->assertTrue($ps->hasError('param_1'));

        $ps2->setValidatorConfig([
            'param_1' => 'boolean',
            'param_2' => ['number', 'min' => 5, 'skipHasError' => false],
        ]);
        $ps2->validate();
        $this->assertTrue($ps2->hasError('param_1'));
        $this->assertFalse($ps2->hasError('param_2'));

        $ps2->validateAllParams = true;
        $ps2->validate();
        $this->assertTrue($ps2->hasError('param_1'));
        $this->assertTrue($ps2->hasError('param_2'));
    }

    //endregion

}