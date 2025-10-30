<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
 * Create the Application
 *
 * The first thing we will do is create a new Laravel application instance
 * which serves as the "glue" for all the components of Laravel, and is
 * the IoC container for the system binding all of the various parts.
 */
return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {})
    ->withExceptions(function (Exceptions $exceptions) {})->create();
