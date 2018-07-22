<?php

namespace liyuze\validator\tests\common;


use liyuze\validator\Mounter\Mounter;

/**
 * Class Bad2Mounter
 * 错误的挂载器，[runMount()] 挂载的值超出 [registerKeys()] 中注册的列表。
 * @package liyuze\validator\tests\common
 */
class Bad2Mounter extends Mounter
{
    public $minYear;

    public function registerKeys()
    {
        return ['name'];
    }

    public function run()
    {
        return '';
    }
}