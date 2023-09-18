<?php
namespace Codificar\Geolocation;
use Illuminate\Support\ServiceProvider;

class GeolocationServiceProvider extends ServiceProvider {

    public function boot()
    {

        // Load routes (carrega as rotas)
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        // Load laravel views (Carregas as views do Laravel, blade)
        $this->loadViewsFrom(__DIR__.'/resources/views', 'geolocation');

        // Load Migrations (Carrega todas as migrations)
        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');

        // Load trans files (Carrega tos arquivos de traducao) 
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'geolocationTrans');

        // Publish the VueJS files inside public folder of main project (Copia os arquivos do vue minificados dessa biblioteca para pasta public do projeto que instalar essa lib)
        $this->publishes([
            __DIR__.'/../public/js' => public_path('vendor/codificar/geolocation'),
        ], 'public_vuejs_libs');

        // Publish the tests files 
        $this->publishes([
            __DIR__ . '/../tests/' => base_path('tests/Unit/libs/geolocation'),
        ], 'publishes_tests');
    }

    public function register()
    {

    }
}
?>