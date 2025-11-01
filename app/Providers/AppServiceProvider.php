<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Compilers\BladeCompiler;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Configure view engine to use PHP as the primary template compiler
        $this->app->singleton('view.engine.resolver', function ($app) {
            $resolver = new \Illuminate\View\Engines\EngineResolver();

            // Register PHP engine FIRST (for plain PHP templates - primary engine)
            $resolver->register('php', function () {
                return new PhpEngine();
            });

            // Register Blade engine as secondary (for potential future use)
            $resolver->register('blade', function () use ($app) {
                $compiler = new BladeCompiler(
                    $app['files'],
                    $app['config']['view.compiled'] ?? storage_path('framework/views')
                );

                return new CompilerEngine($compiler);
            });

            return $resolver;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure PHP templates take precedence over Blade
        // The view finder will look for .php files first
        if ($this->app->has('view')) {
            $this->app['view']->addExtension('php', 'php');
        }
    }
}
