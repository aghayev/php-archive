<?php

namespace App\Middleware;

use App\Request;
use Utils\DateTimeUtils;

class SearchMiddleware implements MiddlewareInterface
{

private array $params;

    public function __construct()
    {
        $this->params  = [0 => 'date', 1 => 'time', 2 => 'headcount'];
    }

    public function handle(Request $request): Request
    {

        foreach ($this->params as $paramPosition => $paramName) {
            if (!$request->has($paramPosition)) {
                throw new MiddlewareException('unset required: '.$paramName);
            }
        }

        if (!DateTimeUtils::validateDateFormat($request->get(0))) {
            throw new MiddlewareException('date must be a string in YYYY-MM-DD format');
        }

        if (!DateTimeUtils::validateTimeFormat($request->get(1))) {
            throw new MiddlewareException('time must be a string in HH:MM format');
        }

        if (0 === (int) $request->get(2)) {
            throw new MiddlewareException('headcount must be integer and above 0');
        }

        return $request;
    }
}