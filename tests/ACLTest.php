<?php

use Silex\Application;
use Groovey\ORM\Providers\ORMServiceProvider;
use Groovey\Support\Providers\TraceServiceProvider;
use Groovey\ACL\Providers\ACLServiceProvider;

class ACLTest extends PHPUnit_Framework_TestCase
{
    public $app;

    public function setUp()
    {
        $app = new Application();
        $app['debug'] = true;

        $app->register(new TraceServiceProvider());
        $app->register(new ACLServiceProvider());

        $app->register(new ORMServiceProvider(), [
            'db.connection' => [
                'host'      => 'localhost',
                'driver'    => 'mysql',
                'database'  => 'test_acl',
                'username'  => 'root',
                'password'  => '',
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
                'logging'   => true,
            ],
        ]);

        $app['db']->connection();
        $app['acl']->load($userId = 1, getcwd().'/resources/yaml/permissions.yml');

        $this->app = $app;
    }

    public function testInit()
    {
        $app   = $this->app;

    }

    public function testPermissions()
    {
        $app   = $this->app;
        $datas = $app['acl']::getPermissions();

        $this->assertContains('template', $datas['template.update']);
    }

    public function testAllow()
    {
        $app = $this->app;
        $app['acl']::setPermission('sample.view', 'value', 'allow');
        $status = $app['acl']->allow('sample.view');

        $this->assertTrue($status);
    }

    public function testDeny()
    {
        $app = $this->app;
        $app['acl']::setPermission('sample.view', 'value', 'deny');
        $status = $app['acl']->deny('sample.view');

        $this->assertTrue($status);
    }

    public function testHelperAllow()
    {
        $app = $this->app;
        $app['acl']::setPermission('sample.view', 'value', 'allow');
        $status = allow('sample.view');
        $this->assertTrue($status);
    }

    public function testHelperDeny()
    {
        $app = $this->app;
        $app['acl']::setPermission('sample.view', 'value', 'deny');
        $status = deny('sample.view');
        $this->assertTrue($status);
    }
}
