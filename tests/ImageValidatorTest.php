<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\ImageValidator;
use liyuze\validator\Validators\InValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class ImageValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\ImageValidator
 */
class ImageValidatorTest extends TestCase
{
    /**
     * 测试基本功能
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testType()
    {
        $validator = new ImageValidator();
        $jpg_file = __DIR__.'/common/test.jpg';
        $excel_file = __DIR__.'/common/test.xlsx';
        $no_exists_file = __DIR__.'/common/test2.xlsx';
        $error = '';
        $this->assertTrue($validator->validate($jpg_file, $error));
        $this->assertFalse($validator->validate($no_exists_file, $error));
        $this->assertEquals('该输入的文件不存在。', $error);
        $this->assertFalse($validator->validate($excel_file, $error));
        $this->assertEquals('该输入的值不是图片文件。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [$jpg_file, ['image']],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, $excel_file);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不是图片文件。', $parameters->getFirstErrorMessage($param_name));
        $parameters->setParamsValue($param_name, $no_exists_file);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的文件不存在。', $parameters->getFirstErrorMessage($param_name));
    }

    public function testExtensions()
    {
        $validator = new ImageValidator(['extensions' => 'png,gif']);
        $jpg_file = __DIR__.'/common/test.jpg';
        $error = '';
        $this->assertFalse($validator->validate($jpg_file, $error));
        $this->assertEquals('该输入的文件只支持以下后缀名：png,gif。', $error);

        $parameters = new Parameters();
        $parameters2 = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [$jpg_file, 'image|extensions=png'],
        ]);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的文件只支持以下后缀名：png。', $parameters->getFirstErrorMessage($param_name));
    }


    public function testMinHeight()
    {
        $validator = new ImageValidator(['minHeight' => 10]);
        $validator2 = new ImageValidator(['minHeight' => 1000]);
        $jpg_file = __DIR__.'/common/test.jpg';
        $error = '';
        $this->assertTrue($validator->validate($jpg_file, $error));
        $this->assertFalse($validator2->validate($jpg_file, $error));
        $this->assertEquals('该输入的图片高度不能小于1000。', $error);

        $parameters = new Parameters();
        $parameters2 = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [$jpg_file, 'image|minHeight=10'],
        ]);
        $parameters2->config([
            $param_name => [$jpg_file, 'image|minHeight=1000'],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters2->validate();
        $this->assertTrue($parameters2->hasError($param_name));
        $this->assertEquals($param_name.'的图片高度不能小于1000。', $parameters2->getFirstErrorMessage($param_name));
    }


    public function testMinWidth()
    {
        $validator = new ImageValidator(['minWidth' => 10]);
        $validator2 = new ImageValidator(['minWidth' => 1000]);
        $jpg_file = __DIR__.'/common/test.jpg';
        $error = '';
        $this->assertTrue($validator->validate($jpg_file, $error));
        $this->assertFalse($validator2->validate($jpg_file, $error));
        $this->assertEquals('该输入的图片宽度不能小于1000。', $error);

        $parameters = new Parameters();
        $parameters2 = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [$jpg_file, 'image|minWidth=10'],
        ]);
        $parameters2->config([
            $param_name => [$jpg_file, 'image|minWidth=1000'],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters2->validate();
        $this->assertTrue($parameters2->hasError($param_name));
        $this->assertEquals($param_name.'的图片宽度不能小于1000。', $parameters2->getFirstErrorMessage($param_name));
    }


    public function testMaxWidth()
    {
        $validator = new ImageValidator(['maxWidth' => 1000]);
        $validator2 = new ImageValidator(['maxWidth' => 10]);
        $jpg_file = __DIR__.'/common/test.jpg';
        $error = '';
        $this->assertTrue($validator->validate($jpg_file, $error));
        $this->assertFalse($validator2->validate($jpg_file, $error));
        $this->assertEquals('该输入的图片宽度不能大于10。', $error);

        $parameters = new Parameters();
        $parameters2 = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [$jpg_file, 'image|maxWidth=1000'],
        ]);
        $parameters2->config([
            $param_name => [$jpg_file, 'image|maxWidth=10'],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters2->validate();
        $this->assertTrue($parameters2->hasError($param_name));
        $this->assertEquals($param_name.'的图片宽度不能大于10。', $parameters2->getFirstErrorMessage($param_name));
    }

    public function testMaxHeight()
    {
        $validator = new ImageValidator(['maxHeight' => 1000]);
        $validator2 = new ImageValidator(['maxHeight' => 10]);
        $jpg_file = __DIR__.'/common/test.jpg';
        $error = '';
        $this->assertTrue($validator->validate($jpg_file, $error));
        $this->assertFalse($validator2->validate($jpg_file, $error));
        $this->assertEquals('该输入的图片高度不能大于10。', $error);

        $parameters = new Parameters();
        $parameters2 = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [$jpg_file, 'image|maxHeight=1000'],
        ]);
        $parameters2->config([
            $param_name => [$jpg_file, 'image|maxHeight=10'],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters2->validate();
        $this->assertTrue($parameters2->hasError($param_name));
        $this->assertEquals($param_name.'的图片高度不能大于10。', $parameters2->getFirstErrorMessage($param_name));
    }
}