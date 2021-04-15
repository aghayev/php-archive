<?php

namespace Utils;

use DateTimeInterface;

function time()
{
    return ClockMock::$frozenTime ?? \time();
}

class ClockMock
{
    public static $frozenTime;

    public static function freezeTime(DateTimeInterface $dateTime): void
    {
        self::$frozenTime = $dateTime->getTimestamp();
    }

    public static function unfreezeTime(): void
    {
        self::$frozenTime = null;
    }
}
