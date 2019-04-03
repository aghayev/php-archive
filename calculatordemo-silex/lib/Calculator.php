<?php

namespace Mrandmrssmith;

class Calculator {

    public static function plus($arguments) {

        return $arguments[0] + $arguments[1];
    }

    public static function minus($arguments) {

        return $arguments[0] - $arguments[1];
    }

    public static function multiplication($arguments) {

        return $arguments[0] * $arguments[1];
    }
    
    public static function division($arguments) {

        return $arguments[0] / $arguments[1];
    }
}
