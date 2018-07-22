<?php

namespace liyuze\validator\tests\common;


use liyuze\validator\Mounter\Mounter;

/**
 * Class BadMounter
 * 错误的挂载器，[registerKeys()] 返回空数组。
 * @package liyuze\validator\tests\common
 */
class BadMounter extends Mounter
{
    public $minYear;

    public function registerKeys()
    {
        return [];
    }

    public function run()
    {
    }
}