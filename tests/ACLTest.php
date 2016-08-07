<?php

use Silex\Application;
use Symfony\Component\Console\Command\Command;
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

        $app->register(new ACLServiceProvider());
        $app->register(new TesterServiceProvider());
        $app->register(new TraceServiceProvider());
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

        $app['db']->connection();

        $container['db'] = $this->app['db'];

        $app['tester']->add([
                new Init($app),
                new Reset($app),
                new Up($app),
                new SeedInit($app),
                new Run($app),
                new Down($app),
                new Drop($app),
            ]);

        $this->app = $app;
    }

    public function testMigrate()
    {
        $app = $this->app;

        $display = $app['tester']->command('migrate:init')->execute()->display();
        $this->assertRegExp('/Sucessfully/', $display);

        $display = $app['tester']->command('migrate:reset')->input('Y\n')->execute()->display();
        $this->assertRegExp('/All migration entries has been cleared/', $display);

        $display = $app['tester']->command('migrate:up')->execute()->display();
        $this->assertRegExp('/Running migration file/', $display);
    }

    public function testSeed()
    {
        $app = $this->app;

        $display = $app['tester']->command('seed:init')->execute()->display();
        $this->assertRegExp('/Sucessfully/', $display);

        $display = $app['tester']->command('seed:run')->execute(['class' => 'Users', 'total' => 5])->display();
        $this->assertRegExp('/End seeding/', $display);

        $display = $app['tester']->command('seed:run')->execute(['class' => 'Permissions', 'total' => 5])->display();
        $this->assertRegExp('/End seeding/', $display);
    }

    public function testLoad()
    {
        $app = $this->app;
        $app['acl']->load($userId = 1, getcwd().'/resources/yaml/permissions.yml');
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

    public function testDrop()
    {
        $app = $this->app;

        $display = $app['tester']->command('migrate:down')->input('Y\n')->execute(['version' => '001'])->display();
        $this->assertRegExp('/Downgrading migration file/', $display);

        $display = $app['tester']->command('migrate:drop')->input('Y\n')->execute()->display();
        $this->assertRegExp('/Migrations table has been deleted/', $display);
    }
}
