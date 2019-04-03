<?php

namespace Mrandmrssmith;

use \Silex\Application as SilexApplication;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

class Application extends SilexApplication
{
    public function __construct()
    {
        parent::__construct();

        $app = $this;
        $app['debug'] = true;

        date_default_timezone_set('Europe/London');

        $app['monolog.options'] = [
            'monolog.logfile' => __DIR__.'/../var/logs/app.log',
            'monolog.name' => 'app',
            // 'monolog.level' => 300, // = Logger::WARNING
        ];

        $app->register(new \Silex\Provider\MonologServiceProvider(), $app['monolog.options']);

        $app->register(
            new \Silex\Provider\SessionServiceProvider(),
            ['session.storage.save_path' => __DIR__.'/../var/sessions']
        );

        $app->register(new \Silex\Provider\SecurityServiceProvider());

        $app['security.default_encoder'] = function ($app) {
            return new PlaintextPasswordEncoder();
        };

        // You have to make sure that the paths defined in the firewall rules are exactly the same
        // as the one in the routes (trailing / inclusive).
        $app['security.firewalls'] = [
            'admin' => [
                'pattern' => '^/admin/',
                'form' => [
                    'login_path' => '/login',
                    'default_target_path' => '/admin/',
                    'check_path' => '/admin/login_check'
                ],
                'logout' => [
                    'logout_path' => '/logout',
                    'target_url' => 'admin',
                    'invalidate_session' => true
                ],
            ],
        ];

        $app->boot();
    }
}