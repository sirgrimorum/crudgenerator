<?php

namespace Sirgrimorum\CrudGenerator;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\AliasLoader;
use Sirgrimorum\CrudGenerator\ExtendedValidator;
use Sirgrimorum\CrudGenerator\CrudGenerator;

class CrudGeneratorServiceProvider extends ServiceProvider {

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot() {
        $this->publishes([
            __DIR__ . '/Config/crudgenerator.php' => config_path('sirgrimorum/crudgenerator.php'),
            __DIR__ . '/Config/models' => config_path('sirgrimorum/models'),
                ], 'config');

        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/Views', 'sirgrimorum');
        $this->publishes([
            __DIR__ . '/Views' => resource_path('views/vendor/sirgrimorum'),
                ], 'views');

        $this->loadTranslationsFrom(__DIR__ . 'Lang', 'crudgenerator');
        $this->publishes([
            __DIR__ . '/Lang' => resource_path('lang/vendor/crudgenerator'),
                ], 'lang');

        /**
         * Assets
         */
        $this->publishes([
            __DIR__ . '/Assets/images' => public_path('vendor/sirgrimorum/images'),
                ], 'assets');
        $this->publishes([
            __DIR__ . '/Assets/ckeditor' => public_path('vendor/sirgrimorum/ckeditor'),
                ], 'assets');
        $this->publishes([
            __DIR__ . '/Assets/jquerytables' => public_path('vendor/sirgrimorum/jquerytables'),
                ], 'assets');
        $this->publishes([
            __DIR__ . '/Assets/slider' => public_path('vendor/sirgrimorum/slider'),
                ], 'assets');
        $this->publishes([
            __DIR__ . '/Assets/confirm' => public_path('vendor/sirgrimorum/confirm'),
                ], 'assets');
        $this->publishes([
            __DIR__ . '/Assets/datetimepicker' => public_path('vendor/sirgrimorum/datetimepicker'),
                ], 'assets');

        /**
         * Extended validator
         */
        Validator::resolver(
                function($translator, $data, $rules, $messages, $customAttributes ) {
            $messages["unique_composite"] = trans("sirgrimorum_cms::admin.error_messages.unique_composite");
            return new ExtendedValidator($translator, $data, $rules, $messages, $customAttributes);
        }
        );
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register() {
        //AliasLoader::getInstance()->alias('CrudLoader', CrudGenerator::class);
        $loader = AliasLoader::getInstance();
        $loader->alias(
                'CrudLoader', \Sirgrimorum\CrudGenerator\CrudGenerator::class
        );

        $this->app->singleton(CrudGenerator::class, function (Application $app) {
            return new CrudGenerator($app);
        });


        //$this->app->alias(, 'CrudLoader');

        $this->mergeConfigFrom(
                __DIR__ . '/Config/crudgenerator.php', 'sirgrimorum.crudgenerator'
        );
    }

}
