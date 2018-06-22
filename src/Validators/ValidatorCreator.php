<?php

namespace liyuze\validator\Validators;

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
        'date'      => '\liyuze\validator\Validators\DatetimeValidator',
        'time'      => '\liyuze\validator\Validators\DatetimeValidator',


        'email'     => '\liyuze\validator\Validators\EmailValidator',
        'url'       => '\liyuze\validator\Validators\UrlValidator',
        'file'      => '\liyuze\validator\Validators\FileValidator',
        'image'     => '\liyuze\validator\Validators\ImageValidator',
        'tmp_file'  => '\liyuze\validator\Validators\TmpFileValidator',


        'compare'   => '\liyuze\validator\Validators\CompareValidator',
        'in'        => '\liyuze\validator\Validators\InValidator',
        'match'     => '\liyuze\validator\Validators\MatchValidator',
        'required'  => '\liyuze\validator\Validators\RequiredValidator',
    ];


    /**
     * 实例化验证器
     * @param string|callable $validator
     * @param array $params
     * @return mixed
     */
    public static function create($validator, $params = [])
    {
        //内建验证器
        if (!is_string($validator)) {
            var_dump($validator);die;
        }

        if (key_exists($validator, self::$built_in_validators)) {
            $config = (array)self::$built_in_validators[$validator];
            $params = array_merge($params, array_slice($config, 1));
            return new $config[0]($params);

        //自定义验证器
        } elseif (class_exists($validator) && $validator instanceof Validator) {

        //可调用函数
        } elseif ($validator instanceof \Closure) {

        //错误
        } else {

        }
    }
}