<?php
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Container\Container;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Facade;

$container = new Container();

Container::setInstance($container);

$container->bind('config', function () {
    return new Illuminate\Config\Repository([
        'database' => [
            'default' => 'mysql',
            'connections' => [
                'mysql' => [
                    'driver' => 'mysql',
                    'database' => env('DB_DATABASE', 'subquery_magic_test'),
                    'username' => env('DB_USERNAME', 'root'),
                    'password' => env('DB_PASSWORD', 'root'),
                    'charset' => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix' => '',
                    'host' => env('DB_HOST', '127.0.0.1'),
                    'port' => env('DB_PORT', 3306),
                    'strict' => true,
                    'engine' => null,
                ],
            ],
            'fetch' => PDO::FETCH_CLASS,
        ]
    ]);
});

$capsule = new Illuminate\Database\Capsule\Manager($container);

// set this instance as global for tests
$capsule->setAsGlobal();
$container->singleton('db', function ($app) use ($capsule) {
    return $capsule->getDatabaseManager();
});
$container->singleton('events', function ($app) {
    return new Dispatcher($app);
});
$capsule->bootEloquent();
$builder = $capsule->getConnection()->getSchemaBuilder();

$capsule->getConnection()->query('CREATE SCHEMA IF NOT EXISTS ' . env('DB_DATABASE', 'subquery_magic_test'));
$builder->dropIfExists('users');
$builder->create('users', function (Blueprint $table) {
    $table->increments('id');
    $table->string('name');
    $table->timestamps();
});
$builder->dropIfExists('comments');
$builder->create('comments', function (Blueprint $table) {
    $table->increments('id');
    $table->string('text');
    $table->unsignedInteger('user_id')->nullable();
    $table->timestamps();
});
Facade::setFacadeApplication($container);