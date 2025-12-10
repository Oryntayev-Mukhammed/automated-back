<?php

namespace Modules\I18n\Providers;

use Illuminate\Support\ServiceProvider;

class I18nServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(module_path('I18n', 'routes/api.php'));
        $this->loadRoutesFrom(module_path('I18n', 'routes/web.php'));
    }
}
