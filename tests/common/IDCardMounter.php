<?php

namespace liyuze\validator\tests\common;

use liyuze\validator\Mounter\Mounter;

class IDCardMounter extends Mounter
{
    public $minYear;

    public function registerKeys()
    {
        return ['year', 'tooMin'];
    }

    public function run()
    {
        $value = $this->getParameter()->getValue();
        if (strlen($value) == 18) {
            $year = substr($value, 6, 4);
        } else {
            $year = '19'.substr($value, 6, 2);
        }
        $year = (int)$year;

        $tooMin = false;
        if ($this->minYear !== null && $year < $this->minYear)
            $tooMin = true;

        return [
            'year' => $year,
            'tooMin' => $tooMin,
        ];
    }
}