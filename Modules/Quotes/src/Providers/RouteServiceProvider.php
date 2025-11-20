<?php

namespace Modules\Quotes\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $namespace = 'Modules\Quotes\Controllers';

    protected string $name = 'Quotes';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapWebRoutes();
        // Future: $this->mapApiRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        $routeDir = module_path('Quotes', 'routes/web');
        foreach (glob($routeDir . '/*.php') as $routeFile) {
            Route::middleware('web')
                ->group($routeFile);
        }
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        // Future API routes implementation
        // Route::prefix('api')
        //     ->middleware('api')
        //     ->group(module_path('Quotes', '/Routes/api/quotes.php'));
    }
}
