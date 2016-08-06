<?php

use Silex\Application;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Command\Command;
use Groovey\DB\Providers\DBServiceProvider;
use Groovey\Support\Providers\TraceServiceProvider;
use Groovey\ACL\Providers\ACLServiceProvider;
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

        $app->register(new TraceServiceProvider());
        $app->register(new ACLServiceProvider());
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

        $this->app = $app;
    }

    public function testMigrate()
    {
        $container['db'] = $this->app['db'];

        $tester = new Tester();
        $tester->command(new Init($container), 'migrate:init');
        $this->assertRegExp('/Sucessfully/', $tester->getDisplay());

        $tester->command(new Reset($container), 'migrate:reset', 'Y\n');
        $this->assertRegExp('/All migration entries has been cleared/',
                $tester->getDisplay());

        $tester->command(new Up($container), 'migrate:up');
        $this->assertRegExp('/Running migration file/', $tester->getDisplay());
    }

    public function testSeed()
    {
        $container['db'] = $this->app['db'];

        $tester = new Tester();
        $tester->command(new SeedInit($container), 'seed:init');
        $this->assertRegExp('/Sucessfully/', $tester->getDisplay());

        $app = new ConsoleApplication();
        $app->add(new Run($container));
        $command = $app->find('seed:run');
        $tester = new CommandTester($command);

        $tester->execute([
                'command' => $command->getName(),
                'class'   => 'Users',
                'total'   => 5,
            ]);

        $this->assertRegExp('/End seeding/', $tester->getDisplay());

        $tester->execute([
                'command' => $command->getName(),
                'class'   => 'Permissions',
                'total'   => 5,
            ]);

        $this->assertRegExp('/End seeding/', $tester->getDisplay());
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
        $container['db'] = $this->app['db'];

        $tester = new Tester();
        $tester->command(new Down($container), 'migrate:down', 'Y\n');
        $tester->command(new Down($container), 'migrate:down', 'Y\n');
        $tester->command(new Down($container), 'migrate:down', 'Y\n');
        $this->assertRegExp('/Downgrading migration file/', $tester->getDisplay());

        $tester->command(new Drop($container), 'migrate:drop', 'Y\n');
        $this->assertRegExp('/Migrations table has been deleted/', $tester->getDisplay());
    }
}
