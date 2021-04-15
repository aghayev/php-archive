<?php

namespace Utils;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DateTimeUtilsTest extends TestCase
{
    public function testNow_ReturnsTrue()
    {
        $this->assertEquals(DateTimeUtils::now(), DateTimeImmutable::createFromFormat('U', time()));
    }

    public function testValidateDateFormat_ReturnsTrue()
    {
        $this->assertTrue(DateTimeUtils::validateDateFormat('2021-03-27','Y-m-d'));
    }

    public function testValidateDateFormat_ReturnsFalse()
    {
        $this->assertFalse(DateTimeUtils::validateDateFormat('27-03-2021','Y-m-d'));
    }

    public function testValidateTimeFormat_ReturnsTrue()
    {
        $this->assertTrue(DateTimeUtils::validateTimeFormat('13:59'));
    }

    public function testValidateTimeFormat_ReturnsFalse()
    {
        $this->assertFalse(DateTimeUtils::validateTimeFormat('13/59'));
    }
}