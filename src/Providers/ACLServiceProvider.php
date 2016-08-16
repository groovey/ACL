<?php

namespace Groovey\ACL\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
use Silex\Api\BootableProviderInterface;
use Groovey\ACL\ACL;

class ACLServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    public function register(Container $app)
    {
        $app['acl'] = function ($app) {

            $permissions = $app['acl.permissions'];

            return new ACL($app, $permissions);
        };
    }

    public function boot(Application $app)
    {
    }
}
