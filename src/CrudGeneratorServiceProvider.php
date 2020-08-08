<?php

namespace Sirgrimorum\CrudGenerator;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\AliasLoader;
use Sirgrimorum\CrudGenerator\ExtendedValidator;
use Illuminate\Support\Facades\Blade;
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
            __DIR__ . '/Config/mimebyext.php' => config_path('sirgrimorum/mimebyext.php'),
            __DIR__ . '/Config/models' => config_path('sirgrimorum/models'),
                ], 'config');

        $this->loadTranslationsFrom(__DIR__ . 'Lang', 'crudgenerator');
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/Views', 'sirgrimorum');
        $this->publishes([
            __DIR__ . '/Views/admin' => resource_path('views/vendor/sirgrimorum/admin'),
                ], 'views');
        $this->publishes([
            __DIR__ . '/Views/templates' => resource_path('views/vendor/sirgrimorum/templates'),
                ], 'templateviews');
        $this->publishes([
            __DIR__ . '/Views/crudgen/create.blade.php' => resource_path('views/vendor/sirgrimorum/crudgen/create.blade.php'),
            __DIR__ . '/Views/crudgen/edit.blade.php' => resource_path('views/vendor/sirgrimorum/crudgen/edit.blade.php'),
            __DIR__ . '/Views/crudgen/error.blade.php' => resource_path('views/vendor/sirgrimorum/crudgen/error.blade.php'),
            __DIR__ . '/Views/crudgen/includes.blade.php' => resource_path('views/vendor/sirgrimorum/crudgen/includes.blade.php'),
            __DIR__ . '/Views/crudgen/list.blade.php' => resource_path('views/vendor/sirgrimorum/crudgen/list.blade.php'),
            __DIR__ . '/Views/crudgen/show.blade.php' => resource_path('views/vendor/sirgrimorum/crudgen/show.blade.php'),
                ], 'crudviews');
        $this->publishes([
            __DIR__ . '/Views/templates' => resource_path('views/vendor/sirgrimorum/templates'),
                ], 'templates');

        $this->publishes([
            __DIR__ . '/Lang' => resource_path('lang/vendor/crudgenerator'),
                ], 'lang');
        $this->publishes([
            __DIR__ . '/Langapp' => resource_path('lang'),
                ], 'langapp');

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
        $this->publishes([
            __DIR__ . '/Assets/select2' => public_path('vendor/sirgrimorum/select2'),
                ], 'assets');
        $this->publishes([
            __DIR__ . '/Assets/typeahead' => public_path('vendor/sirgrimorum/typeahead'),
                ], 'assets');

        /**
         * Extended validator
         */
        Validator::resolver(
                function($translator, $data, $rules, $messages, $customAttributes ) {
            return new ExtendedValidator($translator, $data, $rules, $messages, $customAttributes);
        }
        );
        
        /**
         * Blade directives
         */
        Blade::directive('handleCrudMessages', function($tipo) {
            $tipo = str_replace([' ', '"', "'"], '', $tipo);
            $html = "";
            switch($tipo){
                case 'error':
                    $html = "<?php if (session(config('sirgrimorum.crudgenerator.error_messages_key'))) : ?>".
                        '<div class="container">'.
                        '    <div class="alert alert-danger alert-dismissible fade show" role="alert">'.
                        '        <button type="button" class="close" data-dismiss="alert" aria-label="{{trans("crudgenerator::admin.layout.labels.close")}}"><span aria-hidden="true">&times;</span></button>'.
                        '        {!! session(config("sirgrimorum.crudgenerator.error_messages_key")) !!}'.
                        '    </div>'.
                        '</div>'.
                        "<?php endif; ?>";
                    break;
                case 'status':
                    $html = "<?php if (session(config('sirgrimorum.crudgenerator.status_messages_key'))) : ?>".
                        '<div class="container">'.
                        '    <div class="alert alert-success alert-dismissible fade show" role="alert">'.
                        '        <button type="button" class="close" data-dismiss="alert" aria-label="{{trans("crudgenerator::admin.layout.labels.close")}}"><span aria-hidden="true">&times;</span></button>'.
                        '        {!! session(config("sirgrimorum.crudgenerator.status_messages_key")) !!}'.
                        '    </div>'.
                        '</div>'.
                        "<?php endif; ?>";
                    break;
            }
            //echo $html;
            return $html;
        });
        /**
         * Console commands
         */
        if ($this->app->runningInConsole()) {
            $this->commands([
               Commands\SendAlert::class,
               Commands\CreateModel::class,
               Commands\CreateLang::class,
               Commands\CreateConfig::class,
               Commands\RegisterMiddleware::class,
               Commands\Resources::class,
               Commands\SyncLocal::class,
               Commands\SyncLocalSsh::class,
               Commands\SyncRemote::class,
               Commands\SyncRemoteSsh::class
            ]);
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register() {
        //AliasLoader::getInstance()->alias('CrudLoader', CrudGenerator::class);
        $loader = AliasLoader::getInstance();

        $this->app->singleton(CrudGenerator::class, function (\Illuminate\Foundation\Application $app) {
            return new CrudGenerator($app);
        });
        $loader->alias('CrudGenerator', CrudGenerator::class);


        $this->app->alias(CrudGenerator::class, 'CrudGenerator');

        $this->mergeConfigFrom(
                __DIR__ . '/Config/crudgenerator.php', 'sirgrimorum.crudgenerator'
        );
        $this->mergeConfigFrom(
                __DIR__ . '/Config/mimebyext.php', 'sirgrimorum.mimebyext'
        );
    }

    public function provides() {
        return ['CrudGenerator'];
    }

}
