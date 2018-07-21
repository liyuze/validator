<?php
namespace liyuze\validator\tests;

use liyuze\validator\Exceptions\InvalidArgumentException;
use liyuze\validator\Parameters\Parameter;
use liyuze\validator\Parameters\Parameters;
use liyuze\validator\tests\common\Bad2Mounter;
use liyuze\validator\tests\common\BadMounter;
use liyuze\validator\tests\common\IDCard2Mounter;
use liyuze\validator\tests\common\IDCardMounter;
use liyuze\validator\tests\common\RandMounter;
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
     * @throws
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





    //region 挂载

    /**
     * 基础提供
     * @throws \liyuze\validator\Exceptions\InvalidArgumentException
     */
    public function testBaseMounter()
    {
        $ps = new Parameters([
            'param_1' => '130423199901011234',
            'param_2' => 2
        ]);

        $p = $ps->getParam('param_1');
        $this->assertTrue(true);

        return $p;
    }

    /**
     * 注册的挂载名列表为空
     * @param Parameter $p
     * @depends clone testBaseMounter
     * @throws
     */
    public function testAddMounterRegisterKeysIsEmpty(Parameter $p)
    {
        $BadMounter = new BadMounter($p);
        $this->expectException(InvalidArgumentException::class);
        $p->addMounter($BadMounter);
    }

    /**
     * 注册的挂载名列表有重复
     * @param Parameter $p
     * @depends clone testBaseMounter
     * @throws
     */
    public function testAddMounterRegisterKeysSame(Parameter $p)
    {
        $IDCardMounter = new IDCardMounter($p);
        $IDCard2Mounter = new IDCard2Mounter($p);
        $p->addMounter($IDCardMounter);
        $this->expectException(InvalidArgumentException::class);
        $p->addMounter($IDCard2Mounter);
    }

    /**
     * 获取挂载值
     * @param Parameter $p
     * @depends clone testBaseMounter
     * @throws
     */
    public function testGetMountValue(Parameter $p)
    {
        $IDCardMounter = new IDCardMounter($p);
        $p->addMounter($IDCardMounter);
        $value = $p->getMountValue('year');
        $this->assertEquals(1999, $value);

    }

    /**
     * 无效的挂载值
     * @param Parameter $p
     * @depends clone testBaseMounter
     * @throws
     */
    public function testGetMountValueInvalid(Parameter $p)
    {
        $Bad2Mounter = new Bad2Mounter($p);
        $p->addMounter($Bad2Mounter);
        $this->expectException(InvalidArgumentException::class);
        $p->getMountValue('year');
    }

    /**
     * 无效的挂载名
     * @param Parameter $p
     * @depends clone testBaseMounter
     * @throws
     */
    public function testGetMountValueInvalidName(Parameter $p)
    {
        $this->expectException(InvalidArgumentException::class);
        $p->getMountValue('year');
    }

    /**
     * 缓存挂载值
     * @param Parameter $p
     * @depends clone testBaseMounter
     * @throws
     */
    public function testGetMountValueCache(Parameter $p)
    {
        $RandMounter = new RandMounter($p);
        $p->addMounter($RandMounter);
        $value = $p->getMountValue('rand');
        $value2 = $p->getMountValue('rand');
        $value3 = $p->getMountValue('rand', true);
        $this->assertTrue($value == $value2);
        $this->assertFalse($value == $value3);
    }

    /**
     * 设置挂载缓存。
     * @param Parameter $p
     * @depends clone testBaseMounter
     * @throws
     */
    public function testSetMountValueCache(Parameter $p)
    {
        $this->callPrivateMethod($p, 'setMountValueCache', [['a' => 1, 'b' => 2]]);
        $this->callPrivateMethod($p, 'setMountValueCache', [['c' => 33, 'b' => 22]]);
        $this->assertEquals(1, $p->getMountValue('a'));
        $this->assertEquals(22, $p->getMountValue('b'));
        $this->assertEquals(33, $p->getMountValue('c'));
    }

    //endregion
}