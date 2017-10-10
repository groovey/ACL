<?php

use Silex\Application;
use Groovey\ACL\Providers\ACLServiceProvider;
use Groovey\DB\Providers\DBServiceProvider;
use Groovey\Support\Providers\TraceServiceProvider;
use Groovey\Tester\Providers\TesterServiceProvider;
use Groovey\Migration\Commands\Init;
use Groovey\Migration\Commands\Reset;
use Groovey\Migration\Commands\Up;
use Groovey\Migration\Commands\Down;
use Groovey\Migration\Commands\Drop;
use Groovey\Seeder\Commands\Init as SeedInit;
use Groovey\Seeder\Commands\Run;

class ACLTest extends PHPUnit_Framework_TestCase
{
    public $app;

    public function setUp()
    {
        $app = new Application();
        $app['debug'] = true;

        $app->register(new TesterServiceProvider());
        $app->register(new TraceServiceProvider());

        $app->register(new ACLServiceProvider(), [
                'acl.permissions' => getcwd().'/resources/yaml/permissions.yml',
            ]);

        $app->register(new DBServiceProvider(), [
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

        $app['tester']->add([
                new Init($app),
                new Reset($app),
                new Up($app),
                new SeedInit($app),
                new Run($app),
                new Down($app),
                new Drop($app),
            ]);

        Database::create($app);

        $this->app = $app;
    }

    public function tearDown()
    {
        Database::drop($this->app);
    }

    public function testAuthorize()
    {
        $app = $this->app;
        $app['acl']->authorize($userId = 1);
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
