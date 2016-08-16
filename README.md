# ACL
Groovey Access Control List Package

## Installation

    $ composer require groovey/acl

## Usage

```php
<?php

require_once __DIR__.'/vendor/autoload.php';

use Silex\Application;
use Groovey\DB\Providers\DBServiceProvider;
use Groovey\Support\Providers\TraceServiceProvider;
use Groovey\ACL\Providers\ACLServiceProvider;

$app = new Application();
$app['debug'] = true;

$app->register(new TraceServiceProvider());
$app->register(new ACLServiceProvider(),[
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

$app['db']->connection();

$app['acl']->authorize($userId = 1);
$app['acl']->allow('sample.view');
$app['acl']->deny('sample.view');

allow('sample.view');
deny('sample.view');
```
