<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForecastController;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel's default "home" route.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // You can define custom bindings here if needed
    }

    /**
     * Define the routes for your application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
        // Add any other route maps here, for example for admin routes
    }

    /**
     * Define the "web" routes for the application.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        // Manually register API routes if necessary
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(function () {
                 // Manually define your routes here
                 Route::get('/forecast', [ForecastController::class, 'fetch']);
                 Route::get('/generate-forecast', [ForecastController::class, 'generate']);
                 Route::get('/logs', [ForecastController::class, 'logs']);
                 Route::get('/test-ping', function () {
                     return 'API is working!';
                 });
             });

        // Alternatively, if you want to load them from a file, you can do:
        // Route::prefix('api')
        //      ->middleware('api')
        //      ->namespace($this->namespace)
        //      ->group(base_path('routes/api.php'));
    }

    /**
     * Define custom route groupings, patterns, etc.
     *
     * @return void
     */
    protected function mapAdminRoutes()
    {
        Route::prefix('admin')
             ->middleware(['web', 'auth:admin'])
             ->namespace($this->namespace)
             ->group(base_path('routes/admin.php'));
    }
}
