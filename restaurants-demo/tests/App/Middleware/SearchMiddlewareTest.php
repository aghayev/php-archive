<?php

namespace App\Middleware;

use App\Request;
use App\Middleware\MiddlewareException;
use PHPUnit\Framework\TestCase;

final class SearchMiddlewareTest extends TestCase
{
private SearchMiddleware $middleware;

    public function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SearchMiddleware();
    }

    public function testHandle_UnsetDate_ThrowsMiddlewareException()
    {
        try {
            $request = new Request([]);
            $this->middleware->handle($request);
            $this->fail('Expected time exception not thrown');
        }
        catch(MiddlewareException $e) {
            $this->assertEquals('unset required: date', $e->getMessage());
        }
    }

    public function testHandle_UnsetTime_ThrowsMiddlewareException()
    {
        try {
            $request = new Request(['2021-03-27']);
            $this->middleware->handle($request);
            $this->fail('Expected time exception not thrown');
        }
        catch(MiddlewareException $e) {
            $this->assertEquals('unset required: time', $e->getMessage());
        }
    }

    public function testHandle_UnsetHeadcount_ThrowsMiddlewareException()
    {
        try {
            $request = new Request(['2021-03-27','13:59']);
            $this->middleware->handle($request);
            $this->fail('Expected headcount exception not thrown');
        }
        catch(MiddlewareException $e) {
            $this->assertEquals('unset required: headcount', $e->getMessage());
        }
    }

    public function testHandle_CorrectDateFormat()
    {
        $request = new Request(['2021-03-27','13:59', 5]);
        $this->assertEquals($this->middleware->handle($request), new Request(['2021-03-27','13:59', 5]));
    }

    public function testHandle_IncorrectDateFormat_ThrowsMiddlewareException()
    {
        try {
            $request = new Request(['not-a-date','13:59', 5]);
            $this->middleware->handle($request);
            $this->fail('Expected date format exception not thrown');
        }
        catch(MiddlewareException $e) {
            $this->assertEquals('date must be a string in YYYY-MM-DD format', $e->getMessage());
        }
    }

    public function testHandle_IncorrectTimeFormat_ThrowsMiddlewareException()
    {
        try {
            $request = new Request(['2021-03-27','not:time', 5]);
            $this->middleware->handle($request);
            $this->fail('Expected time format exception not thrown');
        }
        catch(MiddlewareException $e) {
            $this->assertEquals('time must be a string in HH:MM format', $e->getMessage());
        }
    }

    public function testHandle_IncorrectHeadcountString_ThrowsMiddlewareException()
    {
        try {
            $request = new Request(['2021-03-27','13:59', 'abcd']);
            $this->middleware->handle($request);
            $this->fail('Expected time format exception not thrown');
        }
        catch(MiddlewareException $e) {
            $this->assertEquals('headcount must be integer and above 0', $e->getMessage());
        }
    }

    public function testHandle_IncorrectHeadcount0_ThrowsMiddlewareException()
    {
        try {
            $request = new Request(['2021-03-27','13:59', 0]);
            $this->middleware->handle($request);
            $this->fail('Expected time format exception not thrown');
        }
        catch(MiddlewareException $e) {
            $this->assertEquals('headcount must be integer and above 0', $e->getMessage());
        }
    }
}