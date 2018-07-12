<?php

namespace liyuze\validator\Validators;
use liyuze\validator\Exceptions\InvalidArgumentException;
use liyuze\validator\Exceptions\InvalidConfigException;

/**
 * 验证器创建者
 * Class ValidatorCreator
 * @package liyuze\Validators
 */
class ValidatorCreator
{
    /**
     * @var array 内建验证器
     */
    protected static $built_in_validators = [
        'string'    => '\liyuze\validator\Validators\StringValidator',
        'number'    => '\liyuze\validator\Validators\NumberValidator',
        'integer'   => ['\liyuze\validator\Validators\NumberValidator', 'mustInt' => true],
        'boolean'   => '\liyuze\validator\Validators\BooleanValidator',
        'array'     => '\liyuze\validator\Validators\ArrayValidator',

        'datetime'  => '\liyuze\validator\Validators\DatetimeValidator',
        'date'      => ['\liyuze\validator\Validators\DatetimeValidator', 'type' => 'date'],
        'time'      => ['\liyuze\validator\Validators\DatetimeValidator', 'type' => 'time'],

        'email'     => '\liyuze\validator\Validators\EmailValidator',
        'url'       => '\liyuze\validator\Validators\UrlValidator',
        'id_card'   => '\liyuze\validator\Validators\IDCardValidator',
        'file'      => '\liyuze\validator\Validators\FileValidator',
        'image'     => '\liyuze\validator\Validators\ImageValidator',
        'upload_file'  => '\liyuze\validator\Validators\UploadFileValidator',

        'compare'   => '\liyuze\validator\Validators\CompareValidator',
        'in'        => '\liyuze\validator\Validators\InValidator',
        'match'     => '\liyuze\validator\Validators\MatchValidator',
        'required'  => '\liyuze\validator\Validators\RequiredValidator',
        'callable'  => '\liyuze\validator\Validators\CallableValidator',
    ];


    /**
     * 实例化验证器
     * @param string|callable $validator
     * @param array $params
     * @return mixed
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     */
    public static function create($validator, $params = [])
    {
        if (is_string($validator)) {
            //内置验证器
            if (key_exists($validator, self::$built_in_validators)) {
                $config = (array)self::$built_in_validators[$validator];
                $params = array_merge($params, array_slice($config, 1));
                return new $config[0]($params);

            //自定义验证器
            } elseif (class_exists($validator)) {
                $object = new $validator($params);
                if (!($object instanceof Validator)) {
                    throw new InvalidArgumentException('');
                }
                return $object;

            //函数
            } elseif (function_exists($validator)) {
                return new CallableValidator(['method' => $validator]);
            }

        //类或对象的方法
        } elseif (is_array($validator) && count($validator) == 2 && method_exists($validator[0], $validator[1])) {
            return new CallableValidator(['method' => $validator]);

        //匿名函数
        } elseif ($validator instanceof \Closure) {
            return new CallableValidator(['method' => $validator]);
        }

        throw new InvalidArgumentException('Invalid validator value.');
    }
}