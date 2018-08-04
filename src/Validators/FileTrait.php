<?php

namespace liyuze\validator\Validators;

use liyuze\validator\Exceptions\InvalidConfigException;

trait FileTrait
{
    /**
     * @var array|string 允许上传的的文件格式列表
     * 可以是数组或已逗号分隔的字符串列表
     */
    public $_extensions;

    /**
     * @var array|string 允许上传的的文件mime列表。
     */
    public $_mimeTypes;

    /**
     * @var bool mimeType严格模式，是否可以通过文件后缀名来推算文件的 mimeType 值。
     */
    public $mimeTypesStrict = true;

    /**
     * @var mixed
     */
    protected static $_allMimeTypes;

    /**
     * @var integer|string 文件最小Byte大小，默认为null，不限制。
     * 支持格式：510、520k、520Kb、5m、5Mb
     */
    public $minSize;

    /**
     * @var integer|string 文件最大Byte大小，默认为null，不限制。
     * 支持格式：510、520k、520Kb、5m、5Mb
     */
    public $maxSize;

    /**
     * @var string 错误消息
     */
    public $message = '';
    public $messageMinSize = '';
    public $messageMaxSize = '';
    public $messageExtensions = '';
    public $messageMimeTypes = '';

    /**
     * 初始化
     */
    protected function init()
    {
        self::$_allMimeTypes = require __DIR__.'/mimeTypes.php';

        $this->message == '' && $this->message = '{param_name}的文件不存在。';
        $this->messageMinSize == '' && $this->messageMinSize = '{param_name}的文件大小不能小于{min_size}。';
        $this->messageMaxSize == '' && $this->messageMaxSize = '{param_name}的文件大小不能大于{max_size}。';
        $this->messageExtensions == '' && $this->messageExtensions  = '{param_name}的文件只支持以下后缀名：{extensions}。';
        $this->messageMimeTypes == '' && $this->messageMimeTypes  = '{param_name}的文件只支持以下MIME类型：{mime_types}。';
    }

    /**
     * @param $value
     * @throws InvalidConfigException
     */
    public function setExtensions($value)
    {
        if (is_string($value))
            $extensions = explode(',', $value);
        elseif (is_array($value))
            $extensions = $value;
        else
            throw new InvalidConfigException('Invalid extensions value.');

        $this->_extensions = array_map(function($value) {return trim($value);}, $extensions);
    }

    /**
     * @param $value
     * @throws InvalidConfigException
     */
    public function setMimeTypes($value)
    {
        if (is_string($value))
            $mimeTypes = explode(',', $value);
        elseif (is_array($value))
            $mimeTypes = $value;
        else
            throw new InvalidConfigException('Invalid mimeTypes value.');

        $this->_mimeTypes = array_map(function($value) {return trim($value);}, $mimeTypes);
    }

    /**
     * 格式化文件文件大小
     * @param $size
     * @return string
     */
    protected static function formatSize($size)
    {
        $str = '';
        $g = floor($size / 1073741824);
        if ($g) {
            $str .= $g.'GB';
            $size %= 1073741824;
        }
        $m = floor($size / 1048576);
        if ($m) {
            $str .= $m.'MB';
            $size %= 1048576;
        }

        $k = floor($size / 1024);
        if ($k) {
            $str .= $k.'KB';
            $size %= 1024;
        }

        if ($size)
            $str .= $size.'B';

        return $str;
    }

    /**
     * 解析大小字符串
     * @param $sizeString
     * @return int
     */
    protected static function parseSize($sizeString)
    {
        $size = 0;
        $match_count = preg_match_all('/(\d+)([a-zA-Z]*)/', $sizeString, $match);
        if ($match_count) {
            foreach ($match[1] as $k => $v) {
                $unit = strtoupper($match[2][$k]);
                switch ($unit) {
                    case "G":
                    case 'GB':
                        $size += $v * 1073741824;
                        break;
                    case "M":
                    case 'MB':
                        $size += $v * 1048576;
                        break;
                    case "K":
                    case 'KB':
                        $size += $v * 1024;
                        break;
                    default:
                        $size += $v;
                }
            }
        }

        return (int)$size;
    }



    /**
     * 验证 extensions 的值
     * @param $file
     * @return bool
     */
    protected function checkExtensions($file)
    {
        $extension = static::getExtension($file);

        return in_array($extension, $this->_extensions);
    }

    /**
     * 验证 mime type 的值
     * @param $file
     * @return bool
     * @throws InvalidConfigException
     */
    protected function checkMimeTypes($file)
    {
        $mimeType = static::getMimeType($file);

        return in_array($mimeType, $this->_mimeTypes);
    }

    /**
     * 获取文件大小
     */
    protected static function getFileSize($file)
    {
        return filesize($file);
    }

    /**
     * 获取扩展名
     * @param string $file 文件地址
     * @return string
     */
    protected static function getExtension($file)
    {
        return strtolower(pathinfo($file, PATHINFO_EXTENSION));
    }

    /**
     * 获取文件的mimeType
     * @param $file
     * @return mixed|null|string
     * @throws InvalidConfigException
     */
    protected function getMimeType($file)
    {
        if (!extension_loaded('fileinfo')) {
            if (!$this->mimeTypesStrict) {
                return self::getMimeTypeByExtension($file);
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

    /**
     * 通过后缀名获取对应的mimeType
     * @param string $file 文件地址
     * @return null|string
     */
    public static function getMimeTypeByExtension($file)
    {
        $ext = self::getExtension($file);
        if ($ext !== '') {
            if (isset(self::$_allMimeTypes[$ext])) {
                return self::$_allMimeTypes[$ext];
            }
        }

        return null;
    }
}
