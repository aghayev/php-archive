<?php

namespace App;

use App\Controllers\SearchController;
use App\Middleware\MiddlewareException;
use Domain\Vendor\HardcodedVendorRepository;
use Domain\Vendor\VendorDataSourceInterface;
use Domain\Vendor\VendorRepositoryInterface;
use Exception;

class App
{
    private VendorRepositoryInterface $vendorRepository;
    private array $middleware;

    public function __construct(
        array $middleware,
        VendorDataSourceInterface $dataSource
    )
    {
        $this->vendorRepository = new HardcodedVendorRepository($dataSource);
        $this->middleware = $middleware;
    }

    public function run(string $endpoint, array $params): string
    {
        try {
            $request = $this->applyMiddleware($endpoint, new Request($params));
            return $this->dispatchController($endpoint, $request);
        } catch (MiddlewareException $e) {
            return 'Problem when processing request: '.$e->getMessage().PHP_EOL;
        } catch (Exception $e) {
            return "Something's gone terribly wrong, please contact our support: {$e->getMessage()}".PHP_EOL;
        }
    }

    private function applyMiddleware(string $endpoint, Request $request): Request
    {
        if (array_key_exists($endpoint, $this->middleware)) {
            foreach ($this->middleware[$endpoint] as $middlewareClass) {
                $middleware = new $middlewareClass();
                $request = $middleware->handle($request);
            }
        }
        return $request;
    }

    private function dispatchController(string $endpoint, Request $request): string
    {
        switch (strtolower($endpoint)) {
            case 'search':
            {
                $controller = new SearchController($this->vendorRepository);
                return join(PHP_EOL, $controller->searchVendors($request)).PHP_EOL;
            }
            default:
            {
                return 'Unknown endpoint '.$endpoint."\n";
            }
        }
    }
}
