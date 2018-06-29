<?php
namespace liyuze\validator\tests;

use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\UrlValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class UrlValidatorTest
 * @package liyuze\tests
 * @coversDefaultClass \liyuze\validator\Validators\MatchValidator
 */
class UrlValidatorTest extends TestCase
{
    /**
     * @var null|Parameters
     */
    private $_parameters;

    public function setUp()
    {
        $this->_parameters = new Parameters();
        $this->_parameters->config([
            'param_1' => ['http://www.baidu.com', 'url'],
            'param_2' => ['http://www.baidu.coms', ['url', 'patterPart' => [
                UrlValidator::URL_PROTOCOLS, UrlValidator::URL_DOMAIN, UrlValidator::URL_PORT, '?'
            ]]],
        ], true);
    }

    /**
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testType()
    {
        $validator = new UrlValidator();
        $error = '';
        $this->assertTrue($validator->validate('http://www.baidu.com', $error));
        $this->assertTrue($validator->validate('http://127.0.0.1:8888', $error));
        $this->assertTrue($validator->validate('http://www.baidu.com:8888/site/index', $error));
        $this->assertTrue($validator->validate('http://127.0.0.1/site/index?page=1&page_size=20#pos=3', $error));
        $this->assertFalse($validator->validate('www.baidu.com', $error));
        $this->assertEquals('该输入的值不是有效的URL。', $error);

        $param_name = 'param_1';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 'error data');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不是有效的URL。', $parameters->getFirstErrorMessage($param_name));
    }

    /**
     * @covers ::validateParam()
     * @covers ::validate()
     */
    public function testUrlPart()
    {
        //DOMAIN
        $validator = new UrlValidator(['patternPart' => [UrlValidator::URL_DOMAIN]]);
        $error = '';
        $this->assertTrue($validator->validate('www.baidu.com', $error));
        $this->assertFalse($validator->validate('http://www.baidu.com', $error));

        //PROTOCOLS DOMAIN
        $validator = new UrlValidator(['patternPart' => [UrlValidator::URL_PROTOCOLS, '?' ,UrlValidator::URL_DOMAIN]]);
        $error = '';
        $this->assertTrue($validator->validate('www.baidu.com', $error));
        $this->assertTrue($validator->validate('http://www.baidu.com', $error));
        $this->assertTrue($validator->validate('ftp://www.baidu.com', $error));
        $this->assertFalse($validator->validate('http://www.baidu.com/site/index', $error));

        //PROTOCOLS USERNAME DOMAIN
        $validator = new UrlValidator(['patternPart' => [UrlValidator::URL_PROTOCOLS, UrlValidator::URL_USER_INFO,
            UrlValidator::URL_DOMAIN, UrlValidator::URL_PORT, '?'], 'validSchemes' => ['ssh', 'http']]);
        $error = '';
        $this->assertTrue($validator->validate('ssh://git@git.baidu.com:1022', $error));
        $this->assertTrue($validator->validate('ssh://git:123456@git.baidu.com:1022', $error));
        $this->assertFalse($validator->validate('http://www.baidu.com/site/index', $error));

        //QUERY
        $validator = new UrlValidator(['patternPart' => [UrlValidator::URL_QUERY, UrlValidator::URL_FRAGMENT, '?']]);
        $error = '';
        $this->assertTrue($validator->validate('?abc=', $error));
        $this->assertTrue($validator->validate('abc=1&def=2&name=测试', $error));
        $this->assertTrue($validator->validate('abc=1&def=2&name=测试#goods-part', $error));

        $param_name = 'param_2';
        $parameters = $this->_parameters;
        $parameters->validate();
        $this->assertFalse($parameters->hasError($param_name));
        $parameters->setParamsValue($param_name, 'error data');
        $parameters->validate();
        $this->assertTrue($parameters->hasError($param_name));
        $this->assertEquals($param_name.'的值不是有效的URL。', $parameters->getFirstErrorMessage($param_name));
    }
}