<?php

namespace Modules\Signable\App\Providers;

use Illuminate\Support\ServiceProvider;

class SignableServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->modulePath('config/config.php'), 'modules.signable');
        $this->app->register(RouteServiceProvider::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom($this->modulePath('resources/views'), 'signable');
    }

    private function modulePath(string $path = ''): string
    {
        $base = base_path('Modules/Signable');

        return $path === '' ? $base : $base.DIRECTORY_SEPARATOR.$path;
    }
}
