<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use Modules\Signable\App\Providers\SignableServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    SignableServiceProvider::class,
];
