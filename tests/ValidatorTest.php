<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\StringValidator;
use liyuze\validator\Validators\Validator;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\Validator
 */
class ValidatorTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var null|Validator|MockObject
     */
    private $validator;

    public function setUp()
    {
        $this->validator = $this->getMockBuilder(Validator::class)->getMockForAbstractClass();
    }

    /**
     * @covers ::__construct()
     */
    public function testContruct()
    {
        //todo __construct()
    }

    /**
     * @covers ::_set()
     */
    public function testSet()
    {
        $this->validator->skipHasError = false;
        $this->assertEquals($this->validator->skipHasError, false);

        $this->validator->someString = false;
        $this->assertEquals(property_exists($this->validator, 'someString'), false);

        $this->validator->_name = 'tset';
        $this->assertEquals($this->getPrivateProperty($this->validator, '_name'), '');
    }

    /**
     * @covers ::updateValidateStatus()
     */
    public function testUdateValidateStatus()
    {
        $validateStatus = $this->getPrivateProperty($this->validator, '_validateStatus');
        $this->assertEquals($validateStatus, Validator::VALIDATE_STATUS_WAITING);

        $this->callPrivateMethod($this->validator, 'updateValidateStatus', [Validator::VALIDATE_STATUS_DONE]);
        $validateStatus = $this->getPrivateProperty($this->validator, '_validateStatus');
        $this->assertEquals($validateStatus, Validator::VALIDATE_STATUS_DONE);

        $this->callPrivateMethod($this->validator, 'updateValidateStatus', ['error value']);
        $validateStatus = $this->getPrivateProperty($this->validator, '_validateStatus');
        $this->assertEquals($validateStatus, Validator::VALIDATE_STATUS_WAITING);
    }

    /**
     * @covers ::isEmpty()
     */
    public function testIsEmpty()
    {
        //默认
        $isEmpty = $this->validator->isEmpty(null);
        $this->assertEquals($isEmpty, true);
        $isEmpty = $this->validator->isEmpty('');
        $this->assertEquals($isEmpty, true);
        $isEmpty = $this->validator->isEmpty([]);
        $this->assertEquals($isEmpty, true);

        //array
        $this->validator->isEmpty = [null, ''];
        $isEmpty = $this->validator->isEmpty([]);
        $this->assertEquals($isEmpty, false);

        //callable
        $this->validator->isEmpty = function ($value) {
            return $value > 3;
        };
        $isEmpty = $this->validator->isEmpty(4);
        $this->assertEquals($isEmpty, true);
    }

    /**
     * @covers ::validateParam()
     * @covers ::_validateParam()
     */
    public function testValidateParam()
    {
        $param_name = 'param_1';
        $parameters = new Parameters([$param_name => 1]);
        $parameter  = $parameters->getParam($param_name);
        $this->callPrivateMethod($parameter , 'addValidator', [$this->validator]);

        $this->validator->expects($this->any())
            ->method('_validateParam')
            ->will($this->returnValue(true));

        //验证状态
        $validateStatus = $this->getPrivateProperty($this->validator, '_validateStatus');
        $this->assertEquals($validateStatus, Validator::VALIDATE_STATUS_WAITING);
        $this->validator->validateParam($parameter);
        $validateStatus = $this->getPrivateProperty($this->validator, '_validateStatus');
        $this->assertEquals($validateStatus, Validator::VALIDATE_STATUS_DONE);

        //有其他错误时跳过验证
        $parameter->setValue(11);
        $parameters->addError('param_9', 'test',
            '{param_name}的值不能大于{max}', ['max' => 5, 'param_name' => 'param_9']);
        $this->validator->validateParam($parameter);
        $validateStatus = $this->getPrivateProperty($this->validator, '_validateStatus');
        $this->assertEquals($validateStatus, Validator::VALIDATE_STATUS_WAITING);
    }

    /**
     * @covers ::validate()
     * @covers ::_validateValue()
     */
    public function testValidate()
    {
        //验证成功
        $this->validator->expects($this->any())
            ->method('_validateValue')
            ->will($this->returnValue(true));

        $error = '';
        $r = $this->validator->validate(1, $error);
        $this->assertEquals($r, true);
        $this->assertEquals($error, '');
    }

    /**
     * @covers ::validate()
     * @covers ::_validateValue()
     */
    public function testValidateFail()
    {
        //验证失败
        $this->validator->expects($this->any())
            ->method('_validateValue')
            ->will($this->returnValue(['{param_name}的值不能大于{max}' , ['max' => 5]]));

        $error = '';
        $r = $this->validator->validate(9, $error);
        $this->assertEquals($r, false);
        $this->assertEquals($error, '该输入的值不能大于5');
    }

    /**
     * @covers ::formatMessage()
     */
    public function testFormatMessage()
    {
        $message_template = '{param_name}的值不能大于{max}';
        $param = ['max' => 5];
        $message = $this->callPrivateMethod($this->validator, 'formatMessage', [$message_template, $param]);
        $this->assertEquals($message, '该输入的值不能大于5');
    }

    /**
     * @covers ::addError()
     */
    public function testAddError()
    {
        $param_name = 'param_1';
        $parameters = new Parameters([$param_name => 1]);
        $parameter  = $parameters->getParam($param_name);
        $this->callPrivateMethod($this->validator, 'addError', [
            $parameter,
            '{param_name}的值不能大于{max}',
            ['max' => 5],
            'test']);


        $this->assertEquals($parameters->hasError($param_name), true);
        $this->assertEquals($parameters->getFirstErrorMessage($param_name), $param_name.'的值不能大于5');
    }
}