<?php

namespace Modules\Contact\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->routes(function () {
            Route::prefix('api/v1')
                ->middleware('api')
                ->namespace('Modules\\Contact\\Http\\Controllers')
                ->group(module_path('Contact', 'routes/api.php'));
        });
    }
}
