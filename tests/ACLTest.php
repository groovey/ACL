<?php

use Silex\Application;
use Groovey\ACL\Providers\ACLServiceProvider;

class ACLTest extends PHPUnit_Framework_TestCase
{
    public $app;

    public function setUp()
    {
        $app = new Application();
        $app['debug'] = true;

        $app->register(new ACLServiceProvider());

        $this->app = $app;
    }

    public function test()
    {
        $app = $this->app;
    }
}
