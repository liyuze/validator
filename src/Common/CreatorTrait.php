<?php

namespace liyuze\validator\Common;
use liyuze\validator\Exceptions\InvalidArgumentException;
use liyuze\validator\Exceptions\InvalidConfigException;
use liyuze\validator\Parameters\Parameters;
use liyuze\validator\Validators\CallableValidator;
use liyuze\validator\Validators\DatetimeValidator;
use liyuze\validator\Validators\MobilePhoneValidator;
use liyuze\validator\Validators\Validator;

/**
 * 创建者
 * Class ValidatorCreator
 * @package liyuze\Validators
 */
trait CreatorTrait
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
        'date'      => ['\liyuze\validator\Validators\DatetimeValidator', 'type' => DatetimeValidator::TYPE_DATE],
        'time'      => ['\liyuze\validator\Validators\DatetimeValidator', 'type' => DatetimeValidator::TYPE_TIME],

        'email'     => '\liyuze\validator\Validators\EmailValidator',
        'url'       => '\liyuze\validator\Validators\UrlValidator',
        'id_card'   => '\liyuze\validator\Validators\IDCardValidator',
        'mobile_phone'   => '\liyuze\validator\Validators\MobilePhoneValidator',
        'phone'     => ['\liyuze\validator\Validators\MobilePhoneValidator', 'type' => MobilePhoneValidator::TYPE_PHONE],
        'mobile'    => ['\liyuze\validator\Validators\MobilePhoneValidator', 'type' => MobilePhoneValidator::TYPE_MOBILE],

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
     * @var array 用户自定义内置（缩写名）验证器
     */
    public $validator = [];

    /**
     * 创建参数集对象
     * @param array $params
     * @return mixed
     */
    public function createParameters($params = [])
    {
        return new Parameters($params, $this);
    }

    /**
     * @param string|array $rule 验证规则
     * @return array 验证实例数组
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     */
    public function createValidators($rule)
    {
        $config = $this->parseValidatorConfig($rule);
        $validators = [];
        foreach ($config as $v) {
            $validators[] = $this->_createValidator($v[0], array_slice($v, 1));
        }

        return $validators;
    }

    /**
     * 实例化验证器
     * @param string|callable $validator
     * @param array $params
     * @return mixed
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @see parseValidatorConfig()
     */
    public function _createValidator($validator, $params = [])
    {
        if (is_string($validator)) {

            $built_in_validators = $this->getValidator();
            //内置验证器
            if (key_exists($validator, $built_in_validators)) {
                $config = (array)$built_in_validators[$validator];
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

    /**
     * 解析验证器配置
     * @param string|array $data
     *
     * 例：
     * //字符串格式
     * ’required'
     * 'string|maxLength=150|number|mustInt=1'
     * //数组格式
     * ['required']
     *
     * //多验证器以及验证器配置
     * ['required','number', 'max' => 10, 'min' => 1]
     * //或
     * ['required', ['number', 'max' => 10, 'min' => 1]]
     *
     * @return array
     */
    public function parseValidatorConfig($data)
    {
        $validators_config = [];
        if (is_string($data)) {
            //多个验证器或有验证属性
            /**
             * 将：'string|maxLength=150|number|mustInt=1'
             * 解析为：['string', 'maxLength' => 150, 'number', 'mustInt' => true]
             */
            if(strpos($data, '|') > 0) {
                $temp = explode('|', $data);
                $data = [];
                foreach ($temp as $k => $v) {
                    if (strpos($v, '=') > 0) {
                        list($key, $value) = explode('=', $v);
                        $data[$key] = $value;
                    } else
                        $data[] = $v;
                }

                $validators_config = $this->parseValidatorConfig($data);
            } else {
                $validators_config[] = [$data];
            }

        } elseif (is_array($data)) {
            $validator_index = -1;
            foreach ($data as $k => $v) {
                if (is_int($k)) {
                    //验证器
                    if (is_array($v)) {
                        $temp_config = $this->parseValidatorConfig($v);
                        $validator_index += count($temp_config);
                        $validators_config = array_merge($validators_config, $temp_config);
                    } else {
                        $validator_index++;
                        $validators_config[$validator_index][0] = $v;
                    }
                } else {
                    //参数
                    $validators_config[$validator_index][$k] = $v;
                }
            }
        } else {
            //todo 错误配置
        }

        return $validators_config;
    }

    /**
     * 获取验证器
     * @return array
     */
    public function getValidator()
    {
        return array_merge($this->validator, self::$built_in_validators);
    }
}