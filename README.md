# ACL
Groovey Access Control List Package

## Installation

    $ composer require groovey/acl

## Usage

```php
<?php

require_once __DIR__.'/vendor/autoload.php';

use Silex\Application;
use Groovey\ORM\Providers\ORMServiceProvider;
use Groovey\Support\Providers\TraceServiceProvider;
use Groovey\ACL\Providers\ACLServiceProvider;

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
$app['acl']->allow('sample.view');
$app['acl']->deny('sample.view');

allow('sample.view');
deny('sample.view');
```
