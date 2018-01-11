<?php

namespace Solunes\Sales;

use Illuminate\Support\ServiceProvider;

class SalesServiceProvider extends ServiceProvider {

    protected $defer = false;

    public function boot() {
        /* Publicar Elementos */
        $this->publishes([
            __DIR__ . '/config' => config_path()
        ], 'config');
        $this->publishes([
            __DIR__.'/assets' => public_path('assets/sales'),
        ], 'assets');

        /* Cargar Traducciones */
        $this->loadTranslationsFrom(__DIR__.'/lang', 'sales');

        /* Cargar Vistas */
        $this->loadViewsFrom(__DIR__ . '/views', 'sales');
    }


    public function register() {
        /* Registrar ServiceProvider Internos */

        /* Registrar Alias */
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();

        $loader->alias('Sales', '\Solunes\Sales\App\Helpers\Sales');
        $loader->alias('CustomSales', '\Solunes\Sales\App\Helpers\CustomSales');

        /* Comandos de Consola */
        $this->commands([
            //\Solunes\Sales\App\Console\AccountCheck::class,
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/config/sales.php', 'sales'
        );
    }
    
}
