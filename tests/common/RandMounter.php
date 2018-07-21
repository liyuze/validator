<?php

namespace liyuze\validator\tests\common;


use liyuze\validator\Mounter\Mounter;

/**
 * Class RandMounter
 * 随机数挂载器，用于测试挂载值的缓存。
 * @package liyuze\validator\tests\common
 */
class RandMounter extends Mounter
{
    public function registerKeys()
    {
        return ['rand'];
    }

    public function runMount()
    {
        return [
            'rand' => rand(1000, 9999)
        ];
    }
}