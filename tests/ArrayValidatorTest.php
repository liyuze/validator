<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\ArrayValidator;
use liyuze\validator\Validators\StringValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class ArrayValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\ArrayValidator
 */
class ArrayValidatorTest extends TestCase
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

    public function testType()
    {
        $validator = new ArrayValidator();
        $error = '';
        $this->assertTrue($validator->validate([1,2,3], $error));
        $this->assertFalse($validator->validate(1, $error));
        $this->assertEquals('该输入必须是数组类型。', $error);



    }

    public function testKey()
    {

    }

    public function testValue()
    {

    }

    public function testNest()
    {

    }



}