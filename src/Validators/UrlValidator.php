<?php

namespace liyuze\validator\Validators;

use liyuze\validator\Exceptions\InvalidConfigException;
use liyuze\validator\Parameters\Parameter;

class UrlValidator extends Validator
{
    const URL_PROTOCOLS = '({url_schemes}:\/\/)';     //协议
    const URL_USER_INFO = '([a-z0-9]\w*(:\w+)?@)';     //用户账号
    const URL_DOMAIN = '([a-z0-9]([\w]*[a-z0-9])*\.)?[a-z0-9]\w*\.[a-z]{2,}(\.[a-z]{2,})'; //域名
    const URL_PORT = '(:\d{1,5})'; //端口
    const URL_IP = '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}';    //ip
    const URL_ADDRESS = '(\/\S*)'; //地址
    const URL_QUERY = '(([\?&]?[^?&\s]+=[^&\s#]*)+)'; //url参数
    const URL_FRAGMENT = '(#[\w\-.]*)'; //url fragment 参数

    /**
     * @var string 验证器名称
     */
    protected $_name = 'url';

    /**
     * @var string 包含名称的url验证规则
     */
    public $pattern = '/^{url_schemes}:\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(?::\d{1,5})?(?:$|[?\/#])/i';

    /**
     * @var array 验证的url部分
     * [URL_PROTOCOLS, '?', URL_DOMAIN]
     */
    public $patternPart;

    /**
     * @var array 有效的URI schemes
     */
    public $validSchemes = ['http', 'https', 'ftp', 'sftp', 'file', 'git', 'telnet', 'https', 'https', ];

    /**
     * @var string 错误消息
     */
    public $message = '';

    /**
     * UrlValidator constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        if ($this->patternPart !== null) {
            $this->pattern = '/^'.implode('', (array)$this->patternPart).'$/i';
        }

        $this->pattern = str_replace('{url_schemes}', '('.implode('|', $this->validSchemes).')', $this->pattern);

        $this->message == '' && $this->message = '{param_name}的值不是有效的URL。';
    }

    /**
     * 验证参数对象
     * @param Parameter $parameter 参数
     * @return bool
     */
    protected function _validateParam(Parameter $parameter)
    {
        $value = $parameter->getValue();

        if (!preg_match($this->pattern, $value))
            $this->addError($parameter, $this->message);

        return true;
    }

    /**
     * 验证值
     * @param mixed $value 值
     * @return mixed
     */
    protected function _validateValue($value)
    {
        if (!preg_match($this->pattern, $value))
            return $this->message;

        return true;
    }
}
