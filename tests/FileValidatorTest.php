<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\FileValidator;
use liyuze\validator\Validators\ImageValidator;
use liyuze\validator\Validators\InValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class FileValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\FileValidator
 */
class FileValidatorTest extends TestCase
{
    use ReflectionTrait;

    /**
     * 测试基本功能
     * @throws \liyuze\validator\Exceptions\InvalidConfigException
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testType()
    {
        $validator = new FileValidator();
        $jpg_file = __DIR__.'/common/test.jpg';
        $no_exists_file = __DIR__.'/common/test2.xlsx';
        $error = '';
        $this->assertTrue($validator->validate($jpg_file, $error));
        $this->assertFalse($validator->validate($no_exists_file, $error));
        $this->assertEquals('该输入的文件不存在。', $error);


        $parameters = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [$jpg_file, ['image']],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, $no_exists_file);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的文件不存在。', $parameters->getFirstErrorMessage($param_name));
    }

    public function testExtensions()
    {
        $validator = new FileValidator(['extensions' => 'xls,xlsx']);
        $xlsx_file = __DIR__.'/common/test.xlsx';
        $jpg_file = __DIR__.'/common/test.jpg';
        $error = '';
        $this->assertTrue($validator->validate($xlsx_file, $error));
        $this->assertFalse($validator->validate($jpg_file, $error));
        $this->assertEquals('该输入的文件只支持以下后缀名：xls,xlsx。', $error);

        $parameters = new Parameters();
        $parameters2 = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [$xlsx_file, 'file|extensions=xls,xlsx'],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, $jpg_file);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的文件只支持以下后缀名：xls,xlsx。', $parameters->getFirstErrorMessage($param_name));
    }

    public function testMIME()
    {
        $validator = new FileValidator(['MimeTypes' => ' image/png,image/gif']);
        $jpg_file = __DIR__.'/common/test.jpg';
        $error = '';
        $this->assertFalse($validator->validate($jpg_file, $error));
        $this->assertEquals('该输入的文件只支持以下MIME类型：image/png,image/gif。', $error);

        $parameters = new Parameters();
        $parameters2 = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [$jpg_file, 'file|MimeTypes=image/png'],
        ]);
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的文件只支持以下MIME类型：image/png。', $parameters->getFirstErrorMessage($param_name));
    }

    public function testFormatSize()
    {
        $size = 1;
        $size_str = $this->callPrivateMethod(FileValidator::class, 'formatSize', [$size]);
        $this->assertEquals('1', $size_str);
        $size = 2048;
        $size_str = $this->callPrivateMethod(FileValidator::class, 'formatSize', [$size]);
        $this->assertEquals('2K', $size_str);
        $size = 1048576;
        $size_str = $this->callPrivateMethod(FileValidator::class, 'formatSize', [$size]);
        $this->assertEquals('1M', $size_str);
        $size = 1073741824 * 3;
        $size_str = $this->callPrivateMethod(FileValidator::class, 'formatSize', [$size]);
        $this->assertEquals('3G', $size_str);
        $size = 1073741824 + 3072;
        $size_str = $this->callPrivateMethod(FileValidator::class, 'formatSize', [$size]);
        $this->assertEquals('1G3K', $size_str);
    }

    public function testParseSize()
    {
        $size_str = '1g';
        $size = $this->callPrivateMethod(FileValidator::class, 'parseSize', [$size_str]);
        $this->assertEquals(1073741824, $size);
        $size_str = '1mb';
        $size = $this->callPrivateMethod(FileValidator::class, 'parseSize', [$size_str]);
        $this->assertEquals(1048576, $size);
        $size_str = '1K';
        $size = $this->callPrivateMethod(FileValidator::class, 'parseSize', [$size_str]);
        $this->assertEquals(1024, $size);
        $size_str = '1';
        $size = $this->callPrivateMethod(FileValidator::class, 'parseSize', [$size_str]);
        $this->assertEquals(1, $size);

        $size_str = '1GB3KB';
        $size = $this->callPrivateMethod(FileValidator::class, 'parseSize', [$size_str]);
        $this->assertEquals(1073741824 + 3072, $size);
    }

    public function testMinSize()
    {
        $validator = new FileValidator(['minSize' => '1K']);
        $validator2 = new FileValidator(['minSize' => '1mb']);
        $jpg_file = __DIR__.'/common/test.jpg';
        $error = '';
        $this->assertTrue($validator->validate($jpg_file, $error));
        $this->assertFalse($validator2->validate($jpg_file, $error));
        $this->assertEquals('该输入的文件大小不能小于1mb。', $error);

        $parameters = new Parameters();
        $parameters2 = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [$jpg_file, 'file|minSize=10'],
        ]);
        $parameters2->config([
            $param_name => [$jpg_file, 'file|minSize=100000'],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters2->validate();
        $this->assertTrue($parameters2->hasError($param_name));
        $this->assertEquals($param_name.'的文件大小不能小于100000。', $parameters2->getFirstErrorMessage($param_name));
    }


    public function testMaxSize()
    {
        $validator = new FileValidator(['maxSize' => '1M']);
        $validator2 = new FileValidator(['maxSize' => '1K']);
        $jpg_file = __DIR__.'/common/test.jpg';
        $error = '';
        $this->assertTrue($validator->validate($jpg_file, $error));
        $this->assertFalse($validator2->validate($jpg_file, $error));
        $this->assertEquals('该输入的文件大小不能大于1K。', $error);

        $parameters = new Parameters();
        $parameters2 = new Parameters();
        $param_name = 'param_name';
        $parameters->config([
            $param_name => [$jpg_file, 'file|maxSize=1M'],
        ]);
        $parameters2->config([
            $param_name => [$jpg_file, 'file|maxSize=1K'],
        ]);
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters2->validate();
        $this->assertTrue($parameters2->hasError($param_name));
        $this->assertEquals($param_name.'的文件大小不能大于1K。', $parameters2->getFirstErrorMessage($param_name));
    }


}