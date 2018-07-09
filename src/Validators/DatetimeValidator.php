<?php

namespace liyuze\validator\Validators;

use liyuze\validator\Exceptions\Exception;
use liyuze\validator\Exceptions\InvalidConfigException;
use liyuze\validator\Parameters\Parameter;

class DatetimeValidator extends Validator
{
    const TYPE_DATETIME = 'datetime';
    const TYPE_DATE     = 'date';
    const TYPE_TIME     = 'time';

    /**
     * @var string 验证器名称
     */
    protected $_name = 'datetime';

    /**
     * @var string 验证类型
     */
    public $type = self::TYPE_DATETIME;

    /**
     * @var string 指定验证的值的日期时间模式。
     * 应该遵循以下两种日期格式：
     * 第一种是 [ICU manual](http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax) 中的日期时间模式。
     * 第二种是前缀为 `php:` 的字符串，表示为被 PHP Datetime class 解析的格式。
     *
     * 例如:
     *
     * ```php
     * 'MM/dd/yyyy' // ICU 表示格式
     * 'php:m/d/Y' // PHP 表示格式
     * 'MM/dd/yyyy HH:mm' // 包含时间部分的表示格式
     * ```
     */
    public $format;

    /**
     * @var string|int 时间范围开始时间值，可以是时间戳或与format对应的日期时间字符串
     */
    public $start;

    /**
     * @var string|int 时间范围结束时间值，可以是时间戳或与format对应的日期时间字符串
     */
    public $end;


    /**
     * @var string|int start 原始值
     */
    private $_startString;

    /**
     * @var string|int end 原始值
     */
    private $_endString;

    /**
     * @var string 时区
     */
    public $timeZone = 'Asia/Shanghai';

    /**
     * @var string 本地化
     */
    public $locale = 'zh_CN';

    /**
     * @var array 对 IntlDateFormatter 常量值的数组映射的短格式名称
     */
    private static $_dateFormats = [
        'short' => 3, // IntlDateFormatter::SHORT,
        'medium' => 2, // IntlDateFormatter::MEDIUM,
        'long' => 1, // IntlDateFormatter::LONG,
        'full' => 0, // IntlDateFormatter::FULL,
    ];

    /**
     * @var array the php fallback definition to use for the ICU short patterns `short`, `medium`, `long` and `full`.
     * This is used as fallback when the intl extension is not installed.
     */
    public static $phpFallbackDatePatterns = [
        'short' => [
            'date' => 'n/j/y',
            'time' => 'H:i',
            'datetime' => 'n/j/y H:i',
        ],
        'medium' => [
            'date' => 'M j, Y',
            'time' => 'g:i:s A',
            'datetime' => 'M j, Y g:i:s A',
        ],
        'long' => [
            'date' => 'F j, Y',
            'time' => 'g:i:sA',
            'datetime' => 'F j, Y g:i:sA',
        ],
        'full' => [
            'date' => 'l, F j, Y',
            'time' => 'g:i:sA T',
            'datetime' => 'l, F j, Y g:i:sA T',
        ],
    ];

    /**
     * @var bool 去除字符串两边的空格
     */
    public $trimString = true;

    /**
     * @var string 错误消息
     */
    public $message = '';
    public $messageStart = '';
    public $messageEnd = '';

    /**
     * DatetimeValidator constructor.
     * @param array $config
     * @throws InvalidConfigException
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        if (!in_array($this->type, [self::TYPE_DATETIME, self::TYPE_DATE, self::TYPE_TIME]))
            throw new InvalidConfigException('Invalid type value.');

        if (empty($this->format))
        throw new InvalidConfigException('The "format" property must be set.');

        $this->_startString = (string)$this->start;
        $this->_endString = (string)$this->end;

        if ($this->start !== null && is_string($this->start)) {
            $timestamp = $this->parseStrToTimestamp($this->start);
            if ($timestamp === false) {
                throw new InvalidConfigException("Invalid start value: {$this->start}");
            }
            $this->start = $timestamp;
        }

        if ($this->end !== null && is_string($this->end)) {
            $timestamp = $this->parseStrToTimestamp($this->end);
            if ($timestamp === false) {
                throw new InvalidConfigException("Invalid end value: {$this->end}");
            }
            $this->end = $timestamp;
        }

        $this->message == '' && $this->message = '{param_name}的值不是有效的时间值。';
        $this->messageStart == '' && $this->messageStart = '{param_name}的值不能早于{start}。';
        $this->messageEnd == '' && $this->messageEnd = '{param_name}的值不能晚于{end}。';
    }

    /**
     * 验证参数对象
     * @param Parameter $parameter 参数
     * @return mixed
     */
    protected function _validateParam(Parameter $parameter)
    {
        $value = $parameter->getValue();

        $timestamp = $this->parseStrToTimestamp($value);
        if ($timestamp === false) {
            $this->addError($parameter, $this->message);
        } elseif ($this->start !== null && $timestamp < $this->start) {
            $this->addError($parameter, $this->messageStart, ['start' => $this->_startString]);
        } elseif ($this->end !== null && $timestamp > $this->end) {
            $this->addError($parameter, $this->messageEnd, ['end' => $this->_endString]);
        }

        return true;
    }

    /**
     * 验证值
     * @param mixed $value 值
     * @return mixed
     */
    protected function _validateValue($value)
    {
        $timestamp = $this->parseStrToTimestamp($value);
        if ($timestamp === false) {
            return $this->message;
        } elseif ($this->start !== null && $timestamp < $this->start) {
            return [$this->messageStart, ['start' => $this->_startString]];
        } elseif ($this->end !== null && $timestamp > $this->end) {
            return [$this->messageEnd, ['end' => $this->_endString]];
        }

        return true;
    }

    /**
     * 时间字符串转时间戳
     * @param string $value 时间字符串
     * @return false|int
     */
    private function parseStrToTimestamp($value)
    {
        if (strncmp($this->format, 'php:', 4) === 0)
            $format = substr($this->format, 4);
        else {
            if (extension_loaded('intl')) {
                return $this->parseStrToTimestampIntl($value, $this->format);
            }

            $format = self::convertDateIcuToPhp($this->format, $this->type, $this->locale);
        }

        return $this->parseStrToTimestampPHP($value, $format);
    }

    /**
     * 使用 IntlDateFormatter::parse() 解析时间字符串。
     * @param string $value 需要转换的字符串
     * @param string $format 指定字符串的时间格式
     * @return integer|false 时间戳的值或失败
     */
    private function parseStrToTimestampIntl($value, $format)
    {
        if (isset(self::$_dateFormats[$format])) {
            if ($this->type === self::TYPE_DATE) {
                $formatter = new \IntlDateFormatter($this->locale, self::$_dateFormats[$format], \IntlDateFormatter::NONE, $this->timeZone);
            } elseif ($this->type === self::TYPE_DATETIME) {
                $formatter = new \IntlDateFormatter($this->locale, self::$_dateFormats[$format], self::$_dateFormats[$format], $this->timeZone);
            } else {   //self::TYPE_TIME
                $formatter = new \IntlDateFormatter($this->locale, \IntlDateFormatter::NONE, self::$_dateFormats[$format], $this->timeZone);
            }
        } else {
            $hasTimeInfo = (strpbrk($format, 'ahHkKmsSA') !== false);
            $formatter = new \IntlDateFormatter($this->locale, \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, $hasTimeInfo ? $this->timeZone : 'UTC', null, $format);
        }
        // 开启严格验证模式
        $formatter->setLenient(false);

        // There should not be a warning thrown by parse() but this seems to be the case on windows so we suppress it here
        // See https://github.com/yiisoft/yii2/issues/5962 and https://bugs.php.net/bug.php?id=68528
        $parsePos = 0;
        $parsedDate = @$formatter->parse($value, $parsePos);
        if ($parsedDate === false || $parsePos !== mb_strlen($value, 'UTF-8')) {
            return false;
        }

        return $parsedDate;
    }

    /**
     * 使用 the DateTime::createFromFormat() 解析时间字符串。
     * @param string $value 需要转换的字符串
     * @param string $format 指定字符串的时间格式
     * @return integer|false 时间戳的值或失败
     */
    private function parseStrToTimestampPHP($value, $format)
    {
        $hasTimePart = (strpbrk($format, 'HhGgisU') !== false);

        $timezone = $this->timeZone !== null ? new \DateTimeZone($this->timeZone): null;
        $datetime = \DateTime::createFromFormat($format, $value, $timezone);
        $errors = \DateTime::getLastErrors();
        if ($datetime === false || $errors['error_count'])
            return false;

        if (!$hasTimePart)
            $datetime->setTime(0,0,0);

        return $datetime->getTimestamp();
    }

    /**
     * Converts a date format pattern from [ICU format][] to [php date() function format][].
     *
     * The conversion is limited to date patterns that do not use escaped characters.
     * Patterns like `d 'of' MMMM yyyy` which will result in a date like `1 of December 2014` may not be converted correctly
     * because of the use of escaped characters.
     *
     * Pattern constructs that are not supported by the PHP format will be removed.
     *
     * [php date() function format]: http://php.net/manual/en/function.date.php
     * [ICU format]: http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax
     *
     * @param string $pattern date format pattern in ICU format.
     * @param string $type 'date', 'time', or 'datetime'.
     * @param string $locale the locale to use for converting ICU short patterns `short`, `medium`, `long` and `full`.
     * If not given, `Yii::$app->language` will be used.
     * @return string The converted date format pattern.
     */
    public static function convertDateIcuToPhp($pattern, $type = 'date', $locale = null)
    {
        if (isset(self::$_dateFormats[$pattern])) {
            if (extension_loaded('intl')) {
                if ($locale === null) {
                    $locale = Yii::$app->language;
                }
                if ($type === 'date') {
                    $formatter = new \IntlDateFormatter($locale, self::$_dateFormats[$pattern], \IntlDateFormatter::NONE);
                } elseif ($type === 'time') {
                    $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::NONE, self::$_dateFormats[$pattern]);
                } else {
                    $formatter = new \IntlDateFormatter($locale, self::$_dateFormats[$pattern], self::$_dateFormats[$pattern]);
                }
                $pattern = $formatter->getPattern();
            } else {
                return static::$phpFallbackDatePatterns[$pattern][$type];
            }
        }
        // http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax
        // escaped text
        $escaped = [];
        if (preg_match_all('/(?<!\')\'(.*?[^\'])\'(?!\')/', $pattern, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $match[1] = str_replace('\'\'', '\'', $match[1]);
                $escaped[$match[0]] = '\\' . implode('\\', preg_split('//u', $match[1], -1, PREG_SPLIT_NO_EMPTY));
            }
        }

        return strtr($pattern, array_merge($escaped, [
            "''" => "\\'",  // two single quotes produce one
            'G' => '',      // era designator like (Anno Domini)
            'Y' => 'o',     // 4digit year of "Week of Year"
            'y' => 'Y',     // 4digit year e.g. 2014
            'yyyy' => 'Y',  // 4digit year e.g. 2014
            'yy' => 'y',    // 2digit year number eg. 14
            'u' => '',      // extended year e.g. 4601
            'U' => '',      // cyclic year name, as in Chinese lunar calendar
            'r' => '',      // related Gregorian year e.g. 1996
            'Q' => '',      // number of quarter
            'QQ' => '',     // number of quarter '02'
            'QQQ' => '',    // quarter 'Q2'
            'QQQQ' => '',   // quarter '2nd quarter'
            'QQQQQ' => '',  // number of quarter '2'
            'q' => '',      // number of Stand Alone quarter
            'qq' => '',     // number of Stand Alone quarter '02'
            'qqq' => '',    // Stand Alone quarter 'Q2'
            'qqqq' => '',   // Stand Alone quarter '2nd quarter'
            'qqqqq' => '',  // number of Stand Alone quarter '2'
            'M' => 'n',     // Numeric representation of a month, without leading zeros
            'MM' => 'm',    // Numeric representation of a month, with leading zeros
            'MMM' => 'M',   // A short textual representation of a month, three letters
            'MMMM' => 'F',  // A full textual representation of a month, such as January or March
            'MMMMM' => '',
            'L' => 'n',     // Stand alone month in year
            'LL' => 'm',    // Stand alone month in year
            'LLL' => 'M',   // Stand alone month in year
            'LLLL' => 'F',  // Stand alone month in year
            'LLLLL' => '',  // Stand alone month in year
            'w' => 'W',     // ISO-8601 week number of year
            'ww' => 'W',    // ISO-8601 week number of year
            'W' => '',      // week of the current month
            'd' => 'j',     // day without leading zeros
            'dd' => 'd',    // day with leading zeros
            'D' => 'z',     // day of the year 0 to 365
            'F' => '',      // Day of Week in Month. eg. 2nd Wednesday in July
            'g' => '',      // Modified Julian day. This is different from the conventional Julian day number in two regards.
            'E' => 'D',     // day of week written in short form eg. Sun
            'EE' => 'D',
            'EEE' => 'D',
            'EEEE' => 'l',  // day of week fully written eg. Sunday
            'EEEEE' => '',
            'EEEEEE' => '',
            'e' => 'N',     // ISO-8601 numeric representation of the day of the week 1=Mon to 7=Sun
            'ee' => 'N',    // php 'w' 0=Sun to 6=Sat isn't supported by ICU -> 'w' means week number of year
            'eee' => 'D',
            'eeee' => 'l',
            'eeeee' => '',
            'eeeeee' => '',
            'c' => 'N',     // ISO-8601 numeric representation of the day of the week 1=Mon to 7=Sun
            'cc' => 'N',    // php 'w' 0=Sun to 6=Sat isn't supported by ICU -> 'w' means week number of year
            'ccc' => 'D',
            'cccc' => 'l',
            'ccccc' => '',
            'cccccc' => '',
            'a' => 'A',     // AM/PM marker
            'h' => 'g',     // 12-hour format of an hour without leading zeros 1 to 12h
            'hh' => 'h',    // 12-hour format of an hour with leading zeros, 01 to 12 h
            'H' => 'G',     // 24-hour format of an hour without leading zeros 0 to 23h
            'HH' => 'H',    // 24-hour format of an hour with leading zeros, 00 to 23 h
            'k' => '',      // hour in day (1~24)
            'kk' => '',     // hour in day (1~24)
            'K' => '',      // hour in am/pm (0~11)
            'KK' => '',     // hour in am/pm (0~11)
            'm' => 'i',     // Minutes without leading zeros, not supported by php but we fallback
            'mm' => 'i',    // Minutes with leading zeros
            's' => 's',     // Seconds, without leading zeros, not supported by php but we fallback
            'ss' => 's',    // Seconds, with leading zeros
            'S' => '',      // fractional second
            'SS' => '',     // fractional second
            'SSS' => '',    // fractional second
            'SSSS' => '',   // fractional second
            'A' => '',      // milliseconds in day
            'z' => 'T',     // Timezone abbreviation
            'zz' => 'T',    // Timezone abbreviation
            'zzz' => 'T',   // Timezone abbreviation
            'zzzz' => 'T',  // Timezone full name, not supported by php but we fallback
            'Z' => 'O',     // Difference to Greenwich time (GMT) in hours
            'ZZ' => 'O',    // Difference to Greenwich time (GMT) in hours
            'ZZZ' => 'O',   // Difference to Greenwich time (GMT) in hours
            'ZZZZ' => '\G\M\TP', // Time Zone: long localized GMT (=OOOO) e.g. GMT-08:00
            'ZZZZZ' => '',  //  TIme Zone: ISO8601 extended hms? (=XXXXX)
            'O' => '',      // Time Zone: short localized GMT e.g. GMT-8
            'OOOO' => '\G\M\TP', //  Time Zone: long localized GMT (=ZZZZ) e.g. GMT-08:00
            'v' => '\G\M\TP', // Time Zone: generic non-location (falls back first to VVVV and then to OOOO) using the ICU defined fallback here
            'vvvv' => '\G\M\TP', // Time Zone: generic non-location (falls back first to VVVV and then to OOOO) using the ICU defined fallback here
            'V' => '',      // Time Zone: short time zone ID
            'VV' => 'e',    // Time Zone: long time zone ID
            'VVV' => '',    // Time Zone: time zone exemplar city
            'VVVV' => '\G\M\TP', // Time Zone: generic location (falls back to OOOO) using the ICU defined fallback here
            'X' => '',      // Time Zone: ISO8601 basic hm?, with Z for 0, e.g. -08, +0530, Z
            'XX' => 'O, \Z', // Time Zone: ISO8601 basic hm, with Z, e.g. -0800, Z
            'XXX' => 'P, \Z',    // Time Zone: ISO8601 extended hm, with Z, e.g. -08:00, Z
            'XXXX' => '',   // Time Zone: ISO8601 basic hms?, with Z, e.g. -0800, -075258, Z
            'XXXXX' => '',  // Time Zone: ISO8601 extended hms?, with Z, e.g. -08:00, -07:52:58, Z
            'x' => '',      // Time Zone: ISO8601 basic hm?, without Z for 0, e.g. -08, +0530
            'xx' => 'O',    // Time Zone: ISO8601 basic hm, without Z, e.g. -0800
            'xxx' => 'P',   // Time Zone: ISO8601 extended hm, without Z, e.g. -08:00
            'xxxx' => '',   // Time Zone: ISO8601 basic hms?, without Z, e.g. -0800, -075258
            'xxxxx' => '',  // Time Zone: ISO8601 extended hms?, without Z, e.g. -08:00, -07:52:58
        ]));
    }

}
