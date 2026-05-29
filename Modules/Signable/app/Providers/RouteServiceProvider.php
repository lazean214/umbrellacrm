<?php

namespace Modules\Signable\App\Providers;

use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(base_path('Modules/Signable/routes/web.php'));
        $this->loadRoutesFrom(base_path('Modules/Signable/routes/api.php'));
    }
}
