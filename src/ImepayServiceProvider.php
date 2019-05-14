<?php

namespace Shiraj19\ImePay;

use Illuminate\Support\ServiceProvider;

class ImepayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('Shiraj19\ImePay\Imepay');
        $this->loadViewsFrom(__DIR__.'/views', 'Imepay');
        $this->publishFiles();

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        include __DIR__.'/routes.php';
        $this->loadViewsFrom(__DIR__.'/views/', 'ImePay');
    }

    private function publishFiles(){
        $basePath = dirname(__DIR__);
        $arrPublishables = [
            'migrations' => [
                "$basePath/migration" => database_path('migrations'),
            ],
            'config' => [
                "$basePath/config/ime-pay-config.php" => config_path('ime-pay-config.php'),
            ]
        ];

        foreach ($arrPublishables as $group => $paths) {
            $this->publishes($paths, $group);
        }
    }
}
