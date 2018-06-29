<?php

namespace liyuze\validator\Validators;

use liyuze\validator\Exceptions\InvalidConfigException;
use liyuze\validator\Parameters\Parameter;

class UploadFileValidator extends Validator
{
    use FileTrait;

    protected $_name = 'upload_file';

    /**
     * @var integer 最大上传数量，0代表不限制
     */
    public $maxFiles = 0;

    /**
     * @var integer 最小上传数量，0代表不限制
     */
    public $minFiles = 0;


    public $isImage = false;

    /**
     * @var integer 图片尺寸
     */
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
    public $messageMinFiles = '';
    public $messageMaxFiles = '';
    public $messageUploadError = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->message == '' && $this->message = '{param_name}不是上传文件。';
        $this->messageMinSize == '' && $this->messageMinSize = '{param_name}{file_name}文件大小不能低于{min_size}。';
        $this->messageMaxSize == '' && $this->messageMaxSize = '{param_name}{file_name}文件大小不能超过{max_size}。';
        $this->messageExtensions == '' && $this->messageExtensions  = '{param_name}{file_name}文件只支持以下后缀名：{extensions}。';
        $this->messageMimeTypes == '' && $this->messageMimeTypes  = '{param_name}{file_name}文件只支持以下MIME类型：{mime_types}。';
        $this->messageMinFiles == '' && $this->messageMinFiles = '{param_name}最少上传{min_files}个文件。';
        $this->messageMaxFiles == '' && $this->messageMaxFiles = '{param_name}最多上传{max_files}个文件。';
        $this->messageUploadError == '' && $this->messageUploadError = '{param_name}{file_name}上传错误,错误代码：{error_code}。';
        $this->messageNotImage == '' && $this->messageNotImage = '{param_name}{file_name}不是一张图片文件。';
        $this->messageMinWidth == '' && $this->messageMinWidth = '{param_name}图片{file_name}的宽度不能小于{min_width}。';
        $this->messageMaxWidth == '' && $this->messageMaxWidth = '{param_name}图片{file_name}的宽度不能超过{max_width}。';
        $this->messageMinHeight == '' && $this->messageMinHeight  = '{param_name}图片{file_name}的高度不都小于{min_height}。';
        $this->messageMaxHeight == '' && $this->messageMaxHeight  = '{param_name}图片{file_name}的高度不能超过{max_height}。';

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

        //类型验证
        if (!self::checkIsUploadFIle($value)) {
            $this->addError($parameter, $this->message);
            return false;
        }

        //上传文件数
        $files_num = self::getFilesNum($value);
        if ($this->minFiles !== null && $files_num < $this->minFiles)
            $this->addError($parameter, $this->messageMinFiles, ['min_files' => $this->minFiles], 'min_files');
        elseif ($this->maxFiles !== null && $files_num > $this->maxFiles)
            $this->addError($parameter, $this->messageMaxFiles, ['max_files' => $this->maxFiles], 'max_files');

        if (self::isMultipleFiles($value)) {    //多上传文件
            $has_error = false;
            foreach ($value['tmp_name'] as $k => $v) {
                $data = [
                    'name' => $value['name'][$k],
                    'tmp_name' => $value['tmp_name'][$k],
                    'size' => $value['size'][$k],
                    'error' => $value['error'][$k],
                ];
                $this->_validateParamForOne($parameter, $data, $has_error);

                //跳出验证
                if ($has_error) {
                    break;
                }
            }
        } else {    //单个上传文件
            $this->_validateParamForOne($parameter, $value);
        }
    }

    /**
     * 验证参数对象单个值
     * @param $parameter
     * @param array $data
     * @return bool|array
     * @param bool $has_error
     * @return bool
     * @throws InvalidConfigException
     */
    private function _validateParamForOne($parameter, $data, &$has_error = null)
    {
        $name = $data['name'];
        $tmp_name = $data['tmp_name'];
        $size = $data['size'];
        $error = $data['error'];

        //upload error
        if ($error > 0) {
            $this->addError($parameter, $this->messageUploadError, ['error_code' => $error], 'error_code');
            $has_error = true;
            return false;
        }

        //extensions 验证
        if ($this->_extensions !== null && !$this->checkExtensions($name)) {
            $has_error = true;
            $this->addError($parameter, $this->messageExtensions,
                ['extensions' => implode(',', $this->_extensions), ['file_name' => $name]], 'extension');
        }

        //mimeType 验证
        if ($this->_mimeTypes !== null && !$this->checkMimeTypes($tmp_name, $name)) {
            $has_error = true;
            $this->addError($parameter, $this->messageMimeTypes,
                ['mime_types' => implode(',', $this->_mimeTypes), ['file_name' => $name]], 'mime_type');
        }

        //文件大小验证
        if ($this->minSize !== null && $size < self::parseSize($this->minSize)) {
            $has_error = true;
            $this->addError($parameter, $this->messageMinSize, ['min_size' => $this->minSize, ['file_name' => $name]], 'min_size');
        } elseif ($this->maxSize !== null && $size > self::parseSize($this->maxSize)) {
            $has_error = true;
            $this->addError($parameter, $this->messageMaxSize, ['max_size' => $this->maxSize, ['file_name' => $name]], 'max_size');
        }

        //图片验证
        if ($this->isImage === true && false === ($imageInfo = getimagesize($tmp_name))) {
            $this->addError($parameter, $this->messageNotImage, ['file_name' => $name]);
            return false;
        }
        list($width, $height) = $imageInfo;

        //图片尺寸验证
        if ($this->isImage === true && $this->minWidth !== null && $width < $this->minWidth)
            $this->addError($parameter, $this->messageMinWidth, ['min_width' => $this->minWidth, 'file_name' => $name], 'min_width');
        elseif ($this->isImage === true && $this->maxWidth !== null && $width > $this->maxWidth)
            $this->addError($parameter, $this->messageMaxWidth, ['max_width' => $this->maxWidth, 'file_name' => $name], 'max_width');

        if ($this->isImage === true && $this->minHeight !== null && $height < $this->minHeight)
            $this->addError($parameter, $this->messageMinHeight, ['min_height' => $this->minHeight, 'file_name' => $name], 'min_height');
        elseif ($this->isImage === true && $this->maxHeight !== null && $height > $this->maxHeight)
            $this->addError($parameter, $this->messageMaxHeight, ['max_height' => $this->maxHeight, 'file_name' => $name], 'max_height');
    }

    /**
     * 验证值
     * @param mixed $value 值
     * @return array|bool|mixed|string
     * @throws \liyuze\Exceptions\InvalidConfigException
     */
    protected function _validateValue($value)
    {
        //类型验证
        if (!self::checkIsUploadFIle($value)) {
            return $this->message;
        }

        //上传文件数
        $files_num = self::getFilesNum($value);
        if ($this->minFiles !== null && $files_num < $this->minFiles)
            return [$this->messageMinFiles, ['min_files' => $this->minFiles]];
        if ($this->maxFiles !== null && $files_num > $this->maxFiles)
            return [$this->messageMaxFiles, ['max_files' => $this->maxFiles]];

        if (self::isMultipleFiles($value)) {
            foreach ($value['tmp_name'] as $k => $v) {
                $data = [
                    'name' => $value['name'][$k],
                    'tmp_name' => $value['tmp_name'][$k],
                    'size' => $value['size'][$k],
                    'error' => $value['error'][$k],
                ];
                $r = $this->_validateValueForOne($data);
                if ($r !== true)
                    return $r;
            }
        } else {
            return $this->_validateValueForOne($value['name'], $value['tmp_name'], $value['size'], $value['error']);
        }

        return true;
    }

    /**
     * 验证单个值
     * @param array $data
     * @return bool|array
     * @throws InvalidConfigException
     */
    private function _validateValueForOne($data)
    {
        $name = $data['name'];
        $tmp_name = $data['tmp_name'];
        $size = $data['size'];
        $error = $data['error'];

        //upload error
        if ($error > 0) {
            return [$this->messageUploadError, ['code' => $error]];
        }

        //extensions 验证
        if ($this->_extensions !== null && !$this->checkExtensions($name)) {
            return [$this->messageExtensions, ['extensions' => implode(',', $this->_extensions)]];
        }

        //mimeType 验证
        if ($this->_mimeTypes !== null && !$this->checkMimeTypes($tmp_name, $name)) {
            return [$this->messageMimeTypes, ['mime_types' => implode(',', $this->_mimeTypes)]];
        }

        //文件大小验证
        if ($this->minSize !== null && $size < self::parseSize($this->minSize))
            return [$this->messageMinSize, ['min_size' => $this->minSize]];
        if ($this->maxSize !== null && $size > self::parseSize($this->maxSize))
            return [$this->messageMaxSize, ['max_size' => $this->maxSize]];

        //图片验证
        if (false === ($imageInfo = getimagesize($tmp_name))) {
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

        return true;
    }

    /**
     * 检查是否是上传文件
     * @param $value
     * @return bool
     */
    public static function checkIsUploadFIle($value)
    {
        return isset($value['name']) && isset($value['type']) && isset($value['tmp_name']) && isset($value['error'])
            && isset($value['size']);
    }

    /**
     * 获取上传文件数量
     * @param $value
     * @return int
     */
    public static function getFilesNum($value)
    {
        return self::isMultipleFiles($value) ? count($value['tmp_name']) : 1;
    }

    /**
     * 是否是多文件上传模式
     * @param $value
     * @return bool
     */
    public static function isMultipleFiles($value)
    {
        return is_array($value['tmp_name']);
    }

    /**
     * 验证 mime type 的值
     * @param $file
     * @param $file_name
     * @return bool
     * @throws InvalidConfigException
     */
    protected function checkMimeTypes($file, $file_name)
    {
        $mimeType = static::getMimeType($file, $file_name);

        return in_array($mimeType, $this->_mimeTypes);
    }

    /**
     * 获取文件的mimeType
     * @param $file
     * @param $file_name
     * @return mixed|null|string
     * @throws InvalidConfigException
     */
    protected function getMimeType($file, $file_name)
    {
        if (!extension_loaded('fileinfo')) {
            if (!$this->mimeTypesStrict) {
                return self::getMimeTypeByExtension($file_name);
            }

            throw new InvalidConfigException('The fileinfo PHP extension is not installed.');
        }
        $info = finfo_open(FILEINFO_MIME_TYPE);

        if ($info) {
            $result = finfo_file($info, $file);
            finfo_close($info);

            if ($result !== false) {
                return $result;
            }
        }

        return $this->mimeTypesStrict ? static::getMimeTypeByExtension($file) : null;
    }

}
