<?php
namespace Afonso\LvCrud\Providers;

use Illuminate\Support\ServiceProvider;

class LvCrudServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/crud.php' => config_path('crud.php')
        ]);
    }
}
