<?php

namespace liyuze\validator\Validators;

use liyuze\validator\Exceptions\Exception;
use liyuze\validator\Exceptions\InvalidConfigException;
use liyuze\validator\Parameters\Parameter;

class FileValidator extends Validator
{
    use FileTrait;

    protected $_name = 'file';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->init();
    }

    /**
     * 验证参数对象
     * @param Parameter $parameter 参数
     * @return bool
     * @throws InvalidConfigException
     */
    protected function _validateParam(Parameter $parameter)
    {
        $value = $parameter->getValue();

        //存在类型验证
        if (!is_file($value)) {
            $this->addError($parameter, $this->message);
            return false;
        }

        //extensions 验证
        if ($this->_extensions !== null && !$this->checkExtensions($value)) {
            $this->addError($parameter, $this->messageExtensions,
                ['extensions' => implode(',', $this->_extensions)], 'extension');
        }

        //mimeType 验证
        if ($this->_mimeTypes !== null && !$this->checkMimeTypes($value)) {
            $this->addError($parameter, $this->messageMimeTypes,
                ['mime_types' => implode(',', $this->_mimeTypes)], 'mime_type');
        }

        //文件大小验证
        if ($this->minSize !== null && $size = self::getFileSize($value) < self::parseSize($this->minSize))
            $this->addError($parameter, $this->messageMinSize, ['min_size' => $this->minSize], 'min_size');
        if ($this->maxSize !== null && $size = self::getFileSize($value) > self::parseSize($this->maxSize))
            $this->addError($parameter, $this->messageMaxSize, ['max_size' => $this->maxSize], 'max_size');

        return true;
    }

    /**
     * 验证值
     * @param mixed $value 值
     * @return array|bool|mixed|string
     * @throws InvalidConfigException
     */
    protected function _validateValue($value)
    {
        //存在类型验证
        if (!is_file($value)) {
            return $this->message;
        }

        //extensions 验证
        if ($this->_extensions !== null && !$this->checkExtensions($value)) {
            return [$this->messageExtensions, ['extensions' => implode(',', $this->_extensions)]];
        }

        //mimeType 验证
        if ($this->_mimeTypes !== null && !$this->checkMimeTypes($value)) {
            return [$this->messageMimeTypes, ['mime_types' => implode(',', $this->_mimeTypes)]];
        }

        //文件大小验证
        if ($this->minSize !== null && $size = self::getFileSize($value) < self::parseSize($this->minSize))
            return [$this->messageMinSize, ['min_size' => $this->minSize]];
        if ($this->maxSize !== null && $size = self::getFileSize($value) > self::parseSize($this->maxSize))
            return [$this->messageMaxSize, ['max_size' => $this->maxSize]];

        return true;
    }

}
