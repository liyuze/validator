<?php
namespace liyuze\validator\tests;

use liyuze\validator\Exceptions\InvalidArgumentException;
use liyuze\validator\Exceptions\InvalidConfigException;
use liyuze\validator\Parameters\Parameter;
use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\BooleanValidator;
use liyuze\validator\Validators\CallableValidator;
use liyuze\validator\Validators\Validator;
use liyuze\validator\Validators\ValidatorCreator;
use PHPUnit\Framework\TestCase;

/**
 * Class EmailValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\ValidatorCreator
 */
class ValidatorCreatorTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @covers ::create()
     */
    public function testCreate()
    {
        $validatorCreator = new ValidatorCreator();
        //内置验证器
        $built_in_validators = $this->getPrivateProperty($validatorCreator, 'built_in_validators');
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
     * @covers ::create()
     */
    public function testDiy()
    {
        $validatorCreator = new ValidatorCreator();
        //diy验证器
        $booleanValidator = $validatorCreator::create(BooleanValidator::class);
        $this->assertInstanceOf(Validator::class, $booleanValidator);
    }

    /**
     * @covers ::create()
     */
    public function testMethod()
    {
        $validatorCreator = new ValidatorCreator();
        //方法
        $obj = new self();
        $booleanValidator = $validatorCreator::create([$obj, 'methodForTest']);
        $this->assertInstanceOf(CallableValidator::class, $booleanValidator);
        $booleanValidator = $validatorCreator::create([$obj, 'staticMethodForTest']);
        $this->assertInstanceOf(CallableValidator::class, $booleanValidator);
        $booleanValidator = $validatorCreator::create([self::class, 'staticMethodForTest']);
        $this->assertInstanceOf(CallableValidator::class, $booleanValidator);
    }

    /**
     * @covers ::create()
     */
    public function testFunction()
    {
        $validatorCreator = new ValidatorCreator();
        //函数
        $booleanValidator = $validatorCreator::create(__NAMESPACE__.'\funcForTest');
        $this->assertInstanceOf(CallableValidator::class, $booleanValidator);
    }

    /**
     * @covers ::create()
     */
    public function testAnonymous()
    {
        $validatorCreator = new ValidatorCreator();
        //匿名函数
        $booleanValidator = $validatorCreator::create(function () {});
        $this->assertInstanceOf(CallableValidator::class, $booleanValidator);
    }

    /**
     * @covers ::create()
     * @expectedException liyuze\validator\Exceptions\InvalidArgumentException
     */
    public function testThrow()
    {
        $validatorCreator = new ValidatorCreator();
        $booleanValidator = $validatorCreator::create([]);
    }

    public function methodForTest(){}
    public function staticMethodForTest(){}

}

function funcForTest(){}