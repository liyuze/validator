<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameter;
use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\BooleanValidator;
use liyuze\validator\Validators\RequiredValidator;
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
        $this->assertEquals('param_1', $p->getName());
        $this->assertEquals(1, $p->getValue());
        $this->assertEquals('参数1', $p->getAliasOrName());

        $parameters = $this->getPrivateProperty($p, '_parameters');
        if ($parameters)
            $this->assertInstanceOf(Parameters::class, $parameters);

        $p2 = $ps->getParam('param_2');
        $this->assertEquals('param_2', $p2->getAliasOrName());

        return $p;
    }

    /**
     * @param Parameter $p
     * @covers ::validate()
     * @covers ::setValue()
     * @covers ::resetValidateStatus()
     * @depends testConstruct
     */
    public function testValidate(Parameter $p)
    {
        $data = [
            new RequiredValidator(),
            new BooleanValidator(),
        ];
        $p->addValidators($data);

        $p->validate();
        $hasError = $p->getParameters()->hasError($p->getName());
        $this->assertTrue($hasError);

        $validators = $this->getPrivateProperty($p, '_validators');
        if ($validators) {
            $validateStatus = $this->getPrivateProperty($validators[0], '_validateStatus');
            if ($validateStatus)
                $this->assertEquals(Validator::VALIDATE_STATUS_DONE, $validateStatus);
        }

        $p->setValue(true);
        $hasError = $p->getParameters()->hasError($p->getName());
        $this->assertEquals($hasError, false);
        if ($validators) {
            $validateStatus = $this->getPrivateProperty($validators[0], '_validateStatus');
            if ($validateStatus)
                $this->assertEquals(Validator::VALIDATE_STATUS_WAITING, $validateStatus);
        }

        $p->validate();
        $hasError = $p->getParameters()->hasError($p->getName());
        $this->assertFalse($hasError);
    }
}