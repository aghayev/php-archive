<?php

namespace App;

use Exception;

class Request
{
    private array $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function get(int $paramPosition): ?string
    {
        if (!$this->has($paramPosition)) {
            return null;
        }

        if ($paramPosition >= count($this->params)) {
            throw new Exception('Requested param at non-existent position');
        }

        return $this->params[$paramPosition];
    }

    public function has(int $paramPosition): bool
    {
        return isset($this->params[$paramPosition]);
    }
}