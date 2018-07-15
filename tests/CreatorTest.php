<?php
namespace liyuze\validator\tests;

use liyuze\validator\Validators\BooleanValidator;
use liyuze\validator\Validators\CallableValidator;
use liyuze\validator\Validators\Validator;
use liyuze\validator\Creator;
use PHPUnit\Framework\TestCase;

/**
 * Class CreatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Creator
 */
class CreatorTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @covers ::parseValidatorConfig()
     */
    public function testParseValidatorConfig()
    {
        $Creator = new Creator();
        $config = 'string';
        $parseConfig = $this->callPrivateMethod($Creator, 'parseValidatorConfig', [$config]);
        $this->assertSame([['string']], $parseConfig);

        $config = ['string'];
        $parseConfig = $this->callPrivateMethod($Creator, 'parseValidatorConfig', [$config]);
        $this->assertSame([['string']], $parseConfig);

        $config = ['string', 'maxLength' => 150, 'number', 'mustInt' => true];
        $parseConfig = $this->callPrivateMethod($Creator, 'parseValidatorConfig', [$config]);
        $this->assertSame([['string', 'maxLength' => 150], ['number', 'mustInt' => true]], $parseConfig);

        $config = [['string', 'maxLength' => 150], ['number', 'mustInt' => true]];
        $parseConfig = $this->callPrivateMethod($Creator, 'parseValidatorConfig', [$config]);
        $this->assertSame([['string', 'maxLength' => 150], ['number', 'mustInt' => true]], $parseConfig);

        $config = 'string|maxLength=150|number|mustInt=1';
        $parseConfig = $this->callPrivateMethod($Creator, 'parseValidatorConfig', [$config]);
        $this->assertSame([['string', 'maxLength' => '150'], ['number', 'mustInt' => '1']], $parseConfig);
    }

    public function testDefaultValidator()
    {
        $creator = new Creator();
        $param_name = 'param_name';
        $creator->defaultValidateConfig = [
            $param_name => 'string|maxLength=3'
        ];
        $Parameters = $creator->createParameters([$param_name => '55555']);
        $Parameters->validate();
        $this->assertEquals($param_name.'的字符串长度不能大于3。' , $Parameters->getFirstErrorMessage('param_name'));
    }

    /**
     * 测试内置验证器有效性
     * @covers ::_createValidator()
     */
    public function testCreate()
    {
        $creator = new Creator();
        //内置验证器
        $built_in_validators = $this->getPrivateProperty($creator, 'built_in_validators');
        $exists = true;
        foreach ($built_in_validators as $v) {
            $class = is_array($v) ? $v[0] : $v;
            if (!class_exists($class)) {
                $exists = false;
            }
        }
        $this->assertTrue($exists);
    }

    /**
     * 测试diy基层验证器
     * @covers ::_createValidator()
     */
    public function testDiy()
    {
        $creator = new Creator();
        //diy验证器
        $booleanValidator = $creator->_createValidator(BooleanValidator::class);
        $this->assertInstanceOf(Validator::class, $booleanValidator);
    }

    /**
     * 测试类或对象方法
     * @covers ::_createValidator()
     */
    public function testMethod()
    {
        $creator = new Creator();
        //方法
        $obj = new self();
        $booleanValidator = $creator->_createValidator([$obj, 'methodForTest']);
        $this->assertInstanceOf(CallableValidator::class, $booleanValidator);
        $booleanValidator = $creator->_createValidator([$obj, 'staticMethodForTest']);
        $this->assertInstanceOf(CallableValidator::class, $booleanValidator);
        $booleanValidator = $creator->_createValidator([self::class, 'staticMethodForTest']);
        $this->assertInstanceOf(CallableValidator::class, $booleanValidator);
    }

    /**
     * 测试函数
     * @covers ::_createValidator()
     */
    public function testFunction()
    {
        $creator = new Creator();
        //函数
        $booleanValidator = $creator->_createValidator(__NAMESPACE__.'\funcForTest');
        $this->assertInstanceOf(CallableValidator::class, $booleanValidator);
    }

    /**
     * 测试匿名函数
     * @covers ::_createValidator()
     */
    public function testAnonymous()
    {
        $creator = new Creator();
        //匿名函数
        $booleanValidator = $creator->_createValidator(function () {});
        $this->assertInstanceOf(CallableValidator::class, $booleanValidator);
    }

    /**
     * 测试异常
     * @covers ::_createValidator()
     * @expectedException liyuze\validator\Exceptions\InvalidArgumentException
     */
    public function testThrow()
    {
        $creator = new Creator();
        $booleanValidator = $creator->_createValidator([]);
    }

    /**
     * 测试所用
     */
    public function methodForTest(){}

    /**
     * 测试所用
     */
    public static function staticMethodForTest(){}

}

/**
 * 测试所用
 */
function funcForTest(){}