<?php

namespace Utils;

use DateTime;

class DateTimeUtils
{
    public static function getCurrentTS() {
        // Temporarily added to mimic right php.ini setup
        date_default_timezone_set('Europe/London');

        $date = new DateTime();
        return $date->getTimestamp();
    }

    public static function getHeader() {

    }

    public static function getContent() {

    }

    public static function getXVarnishIp($header) {

    }
}