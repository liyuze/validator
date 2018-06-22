<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameter;
use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Class ParameterTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Parameters\Parameter
 */
class ParameterTest extends TestCase
{
    use ReflectionTrait;

    private $_params;

    protected function setUp()
    {
        $this->_params = new Parameters();
    }

    /**
     * @covers ::__construct()
     * @return Parameter
     */
    public function testConstruct()
    {
        $ps = new Parameters([
            'param_1' => [1, '参数1'],
            'param_2' => 2
        ]);

        $p = $ps->getParam('param_1');
        $this->assertEquals($p->getName(), 'param_1');
        $this->assertEquals($p->getValue(), 1);
        $this->assertEquals($p->getAliasOrName(), '参数1');

        $parameters = $this->getPrivateProperty($p, '_parameters');
        if ($parameters)
            $this->assertInstanceOf(Parameters::class, $parameters);

        $p2 = $ps->getParam('param_2');
        $this->assertEquals($p2->getAliasOrName(), 'param_2');

        return $p;
    }

    /**
     * @param Parameter $p
     * @depends testConstruct
     * @covers ::setValidatorConfig()
     * @covers ::getValidatorConfig()
     * @return Parameter
     */
    public function testSetValidatorConfig(Parameter $p)
    {
        $p->setValidatorConfig([['required'],['string']]);

        $config = $p->getValidatorConfig();
        $this->assertSame($config, [['required'],['string']]);

        $validators = $this->getPrivateProperty($p, '_validators');
        if ($validators)
            $this->assertInstanceOf(Validator::class, $validators[1]);

        return $p;
    }

    /**
     * @param Parameter $p
     * @covers ::validate()
     * @covers ::setValue()
     * @covers ::resetValidateStatus()
     * @depends testSetValidatorConfig
     */
    public function testValidate(Parameter $p)
    {
        $p->validate();
        $hasError = $p->getParameters()->hasError($p->getName());
        $this->assertEquals($hasError, true);

        $validators = $this->getPrivateProperty($p, '_validators');
        if ($validators) {
            $validateStatus = $this->getPrivateProperty($validators[0], '_validateStatus');
            if ($validateStatus)
                $this->assertEquals($validateStatus, Validator::VALIDATE_STATUS_DONE);
        }

        $p->setValue('string');
        $hasError = $p->getParameters()->hasError($p->getName());
        $this->assertEquals($hasError, false);
        if ($validators) {
            $validateStatus = $this->getPrivateProperty($validators[0], '_validateStatus');
            if ($validateStatus)
                $this->assertEquals($validateStatus, Validator::VALIDATE_STATUS_WAITING);
        }

        $p->validate();
        $hasError = $p->getParameters()->hasError($p->getName());
        $this->assertEquals($hasError, false);
    }
}