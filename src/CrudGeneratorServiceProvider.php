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
        //Add the scriptsLoader funtion to load scripts only once
        Blade::directive('addScriptsLoader', function () {
            $name = config("sirgrimorum.crudgenerator.scriptLoader_name","scriptLoader");
            $html = "<script>".
            "var callbacksFunctions = [];".
            "function {$name}Creator(callbackName, functionBody){".
                "if(!(callbackName in callbacksFunctions)){".
                    "callbacksFunctions[callbackName] = [];".
                "}".
                "callbacksFunctions[callbackName].push(new Function(functionBody));".
            "}".
            "function {$name}Runner(callbackName){".
                "if(callbackName in callbacksFunctions){".
                    "for (var i = 0; i < callbacksFunctions[callbackName].length; i++){".
                        "callbacksFunctions[callbackName][i]();".
                    "}".
                "}".
            "}".
            "function $name(path, diferir, inner=''){".
                "let scripts = Array .from(document.querySelectorAll('script')).map(scr => scr.src);".
                "var callbackName = inner;".
                "if (inner=='' && path != ''){".
                    "callbackName = path.split('/').pop().split('#')[0].split('?')[0].replaceAll('.','_');".
                "}".
                "if (!scripts.includes(path) || path == ''){".
                    "var tag = document.createElement('script');".
                    "tag.type = 'text/javascript';".
                    "if (callbackName!= ''){".
                        "if(tag.readyState) {".
                            "tag.onreadystatechange = function() {".
                                "if ( tag.readyState === 'loaded' || tag.readyState === 'complete' ) {".
                                    "tag.onreadystatechange = null;".
                                    "{$name}Runner(callbackName);".
                                "}".
                            "};".
                        "}else{".
                            "tag.onload = function() {".
                                "{$name}Runner(callbackName);".
                            "};".
                        "}".
                    "}".
                    "if (path != ''){".
                        "tag.src = path;".
                    "}".
                    "if (diferir){".
                        "var attd = document.createAttribute('defer');".
                        "tag.setAttributeNode(attd);".
                    "}".
                    "if (inner != ''){".
                        "var innerBlock = document.getElementById(inner);" .
                        "if (typeof innerBlock !== 'undefined' && innerBlock !== null){" .
                            "tag.innerHTML = innerBlock.innerHTML;".
                        "}" .
                    "}".
                    "document.getElementsByTagName('body')[document.getElementsByTagName('body').length-1].appendChild(tag);".
                "}else{".
                    "if (callbackName!= ''){".
                        "if(callbackName in window){window[callbackName]();}".
                    "}".
                "}".
            "}".
            "</script>";
            return $html;
        });
        Blade::directive('loadScript', function ($expression) {
            $auxExpression = explode(',', str_replace(['(', ')', ' ', '"', "'"], '', $expression));
            if (count($auxExpression) > 2) {
                $src = $auxExpression[0];
                $defer = $auxExpression[1] == "true";
                $inner = "<?php echo \"" .  $auxExpression[2] . "\"; ?>";
            } elseif (count($auxExpression) > 1) {
                $src = $auxExpression[0];
                $defer = $auxExpression[1] == "true";
                $inner = "";
            } else {
                $src = $auxExpression[0];
                $defer = false;
                $inner = "";
            }
            return CrudGenerator::addScriptLoaderHtml($src, $defer, $inner, true);
        });
        //Add the linkTagssLoader funtion to load scripts only once
        Blade::directive('addLinkTagsLoader', function () {
            $name = config("sirgrimorum.crudgenerator.linkTagLoader_name","linkTagLoader");
            $html = "<script>".
            "function $name(path, rel = 'stylesheet', type = 'text/css'){".
                "let links = Array .from(document.querySelectorAll('link')).map(href => href.href);".
                "if (!links.includes(path) || path == ''){".
                    "var tag = document.createElement('link');".
                    "tag.type = type;".
                    "tag.rel = rel;".
                    "if (path != ''){".
                        "tag.href = path;".
                    "}".
                    "document.getElementsByTagName('head')[document.getElementsByTagName('head').length-1].appendChild(tag);".
                "}".
            "}".
            "</script>";
            return $html;
        });
        Blade::directive('loadLinkTag', function ($expression) {
            $auxExpression = explode(',', str_replace(['(', ')', ' ', '"', "'"], '', $expression));
            if (count($auxExpression) > 2) {
                $href = $auxExpression[0];
                $rel = $auxExpression[1];
                $type = $auxExpression[2];
            } elseif (count($auxExpression) > 1) {
                $href = $auxExpression[0];
                $rel = $auxExpression[1];
                $type = "text/css";
            } else {
                $href = $auxExpression[0];
                $rel = "stylesheet";
                $type = "text/css";
            }
            return CrudGenerator::addLinkTagLoaderHtml($href, $rel, $type);
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
