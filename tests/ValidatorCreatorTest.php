<?php
namespace liyuze\validator\tests;

use liyuze\validator\Exceptions\InvalidConfigException;
use liyuze\validator\Parameters\Parameter;
use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\BooleanValidator;
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
        $built_in_validators = $this->getPrivateProperty($validatorCreator, 'built_in_validators');
        $exists = true;
        foreach ($built_in_validators as $v) {
            $class = is_array($v) ? $v[0] : $v;
            if (!class_exists($class)) {
                $exists = false;
            }
        }
        $this->assertTrue($exists);

        $booleanValidator = $validatorCreator::create(BooleanValidator::class);
        var_dump($booleanValidator);die;

    }

}

class MultiplierValidator extends Validator {

    protected $_name = 'multiplier';

    /**
     * @var string 错误消息
     */
    public $message;

    /**
     * @var integer|float 基础数
     */
    public $number;

    /**
     * EqString constructor.
     * @param array $config
     * @throws InvalidConfigException
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        if ($this->number === null) {
            throw new InvalidConfigException('The "number" property must be set.');
        }

        if (!is_numeric($this->number)) {
            throw new InvalidConfigException('The "number" property must be number type.');
        }

        $this->message === null && $this->message = '{param_name}的值必须是{number}的倍数';
    }

    protected function _validateParam(Parameter $parameter)
    {
        $value = $parameter->getValue();
        if ($value % $this->number !== 0) {
            $this->addError($parameter, $this->message, ['number' => $this->number]);
        }
    }

    protected function _validateValue($value)
    {
        if ($value % $this->number !== 0) {
            return [$this->message, ['number' => $this->number]];
        }
    }

}