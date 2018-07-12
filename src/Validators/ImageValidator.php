<?php

namespace liyuze\validator\Validators;

use liyuze\validator\Exceptions\InvalidConfigException;
use liyuze\validator\Parameters\Parameter;

class ImageValidator extends FileValidator
{
    protected $_name = 'image';

    public $minWidth;
    public $maxWidth;
    public $minHeight;
    public $maxHeight;

    /**
     * @var string 错误消息
     */
    public $messageNotImage = '';
    public $messageMinWidth = '';
    public $messageMaxWidth = '';
    public $messageMinHeight = '';
    public $messageMaxHeight = '';
    public $messageExtensions = '';
    public $messageMimeTypes = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        self::$_allMimeTypes = require __DIR__.'mimeTypes.php';

        $this->messageNotImage == '' && $this->messageNotImage = '{param_name}的值不是一张图片文件。';
        $this->messageMinWidth == '' && $this->messageMinWidth = '{param_name}图片宽度不能小于{min_width}。';
        $this->messageMaxWidth == '' && $this->messageMaxWidth = '{param_name}图片宽度不能超过{max_width}。';
        $this->messageMinHeight == '' && $this->messageMinHeight  = '{param_name}图片高度不都小于{min_height}。';
        $this->messageMaxHeight == '' && $this->messageMaxHeight  = '{param_name}图片高度不能超过{max_height}。';
    }

    /**
     * 验证参数对象
     * @param Parameter $parameter 参数
     * @return bool
     * @throws InvalidConfigException
     */
    protected function _validateParam(Parameter $parameter)
    {
        if (parent::_validateParam($parameter) !== false) {
            $value = $parameter->getValue();

            //图片验证
            if (false === ($imageInfo = getimagesize($value))) {
                $this->addError($parameter, $this->messageNotImage);
                return false;
            }
            list($width, $height) = $imageInfo;

            //图片尺寸验证
            if ($this->minWidth !== null && $width < $this->minWidth)
                $this->addError($parameter, $this->messageMinWidth, ['min_width' => $this->minWidth], 'min_width');
            elseif ($this->maxWidth !== null && $width > $this->maxWidth)
                $this->addError($parameter, $this->messageMaxWidth, ['max_width' => $this->maxWidth], 'max_width');

            if ($this->minHeight !== null && $height < $this->minHeight)
                $this->addError($parameter, $this->messageMinHeight, ['min_height' => $this->minHeight], 'min_height');
            elseif ($this->maxHeight !== null && $height > $this->maxHeight)
                $this->addError($parameter, $this->messageMaxHeight, ['max_height' => $this->maxHeight], 'max_height');
        }

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
        if (parent::validateValue($value) === true) {
            //图片验证
            if (false === ($imageInfo = getimagesize($value))) {
                return $this->messageNotImage;
            }
            list($width, $height) = $imageInfo;

            //图片尺寸验证
            if ($this->minWidth !== null && $width < $this->minWidth)
                return [$this->messageMinWidth, ['min_width' => $this->minWidth]];
            elseif ($this->maxWidth !== null && $width > $this->maxWidth)
                return [$this->messageMaxWidth, ['max_width' => $this->maxWidth]];

            if ($this->minHeight !== null && $height < $this->minHeight)
                return [$this->messageMinHeight, ['min_height' => $this->minHeight]];
            elseif ($this->maxHeight !== null && $height > $this->maxHeight)
                return [$this->messageMaxHeight, ['max_height' => $this->maxHeight]];
        }

        return true;
    }

}
