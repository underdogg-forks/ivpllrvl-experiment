<?php

/**
 * Bootstrap the Illuminate application
 */

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;

// Create the container
$container = Container::getInstance();

// Set up Facade
Facade::setFacadeApplication($container);

// Database configuration
$capsule = new Capsule();

// Add database connection from environment
$capsule->addConnection([
    'driver' => env('DB_DRIVER', 'mysql'),
    'host' => env('DB_HOSTNAME', 'localhost'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'invoiceplane'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => null,
]);

// Make this Capsule instance available globally via static methods
$capsule->setAsGlobal();

// Setup the Eloquent ORM
$capsule->bootEloquent();

// Bind database into container
$container->singleton('db', function () use ($capsule) {
    return $capsule->getDatabaseManager();
});

// Setup View
$filesystem = new Filesystem();
$container->singleton('files', function () use ($filesystem) {
    return $filesystem;
});

$container->singleton('events', function () {
    return new Dispatcher();
});

// View paths - include both new Modules and legacy application/modules
$viewPaths = [
    __DIR__ . '/../Modules',
    __DIR__ . '/../resources/views',
    __DIR__ . '/../application/modules',
    __DIR__ . '/../application/views',
];

$container->singleton('view.finder', function ($app) use ($viewPaths) {
    return new FileViewFinder($app['files'], $viewPaths);
});

$container->singleton('view.engine.resolver', function ($app) {
    $resolver = new EngineResolver();

    // Register PHP engine (for plain PHP templates)
    $resolver->register('php', function () {
        return new PhpEngine();
    });

    // Register Blade engine if needed in future
    $resolver->register('blade', function () use ($app) {
        $compiler = new BladeCompiler($app['files'], __DIR__ . '/../storage/framework/views');
        return new CompilerEngine($compiler);
    });

    return $resolver;
});

$container->singleton('view', function ($app) {
    $factory = new Factory(
        $app['view.engine.resolver'],
        $app['view.finder'],
        $app['events']
    );

    // Set the container
    $factory->setContainer($app);

    return $factory;
});

return $container;
