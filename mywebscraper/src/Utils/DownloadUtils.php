<?php

namespace Utils;

class DownloadUtils
{
    public static function getDownloadPath($folders) {
        // Temporarily added to mimic right php.ini setup
        date_default_timezone_set('Europe/London');

        $date = new DateTime();
        return $date->getTimestamp();
    }
}