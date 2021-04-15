<?php

namespace Utils;

use DateTime;
use DateTimeImmutable;

class DateTimeUtils
{
    public static function now(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromFormat('U', time());
    }

    public static function validateDateFormat($date, $format = 'Y-m-d'): bool
    {
        $ftDate = DateTime::createFromFormat($format, $date);
        return $ftDate && $ftDate->format($format) === $date;
    }

    public static function validateTimeFormat($time): bool
    {
        return preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $time);
    }
}