<?php

namespace App\Middleware;

use App\Request;

interface MiddlewareInterface
{
    public function handle(Request $request): Request;
}
