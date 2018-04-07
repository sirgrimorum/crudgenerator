<?php

namespace Sirgrimorum\CrudGenerator;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\AliasLoader;
use Sirgrimorum\CrudGenerator\ExtendedValidator;
use Illuminate\Support\Facades\Blade;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use Illuminate\Support\Facades\Artisan;
use Pusher\Pusher;

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
        Artisan::command('crudgen:sendalert ', function () {
            $alertaClass = $this->ask("Notification Class?");
            if (class_exists($alertaClass)) {
                $email = $this->anticipate("User email?", ['andres.espinosa@grimorum.com']);
                if ($toUser = \App\User::where("email", "=", $email)->first()) {
                    $message = $this->ask("Message?");
                    /* $options = array(
                      'cluster' => 'us2',
                      'encrypted' => true
                      );
                      $pusher = new Pusher(
                      'adf503a8756876656ec6', 'b21a0ee639fbb893e41d', '481076', $options
                      );
                     */
                    $data = [];
                    $data['message'] = $message;
                    /* $result = $pusher->trigger('my-channel', 'my-event', $data, null, true);
                      $this->line(print_r($result, true)); */
                    $tiempo = $this->ask("Send in? (Just a number,next you will select seconds, minutes, etc.)");
                    if (!is_int((int) $tiempo)) {
                        $tiempo = $this->ask("Send in? HAS TO BE AN INTEGER NUMBER");
                    }
                    if (is_int((int) $tiempo)) {
                        $unidad = $this->choice('...', ['seconds', 'minutes', 'hours', 'days'], 0);
                        switch ($unidad) {
                            case 'seconds':
                                $when = now()->addSeconds($tiempo);
                                break;
                            case 'minutes':
                                $when = now()->addMinutes($tiempo);
                                break;
                            case 'hours':
                                $when = now()->addHours($tiempo);
                                break;
                            case 'days':
                                $when = now()->addDays($tiempo);
                                break;
                            default:
                                $when = now()->addMinutes($tiempo);
                                break;
                        }
                        $toUser->notify((new $alertaClass($data))
                                        ->delay($when)
                        );
                        $this->info("Listo!");
                    } else {
                        $this->error("Paila!");
                    }
                } else {
                    $this->error("User not found");
                }
            } else {
                $this->error("Class not found");
            }
        })->describe('Broadcast an alert to a user');

        Artisan::command('crudgen:createmodel {table : The Table name} {--path= : Provide a custom paht for saving the model file relative to base_path()}', function ($table) {
            $this->line("Preparing model attributes");
            $bar = $this->output->createProgressBar(4);
            $options = $this->options('path');
            //$modelName = $singular = substr($table, 0, strlen($table) - 1);
            $modelName = $singular = str_singular($table);
            if ($options['path'] != "") {
                $path = $options['path'];
            } else {
                $path = "app/" . ucfirst($modelName);
            }
            $path = str_replace("//", "/", str_replace(["\\", " "], ["/", ""], $path));
            $AuxclassName = explode("/", str_replace(".php", "", $path));
            $className = "";
            $justPath = "";
            $prefijoPath = "/";
            $prefijo = "";
            $fileName = "";
            $nameSpace = "";
            foreach ($AuxclassName as $indice => $pedazo) {
                if ($indice == count($AuxclassName) - 1) {
                    $nameSpace = $className;
                    $fileName = str_finish(ucfirst($pedazo), ".php");
                    $modelName = strtolower($pedazo);
                } else {
                    $justPath .=$prefijoPath . $pedazo;
                    $prefijoPath = "/";
                }
                $className .= $prefijo . ucfirst($pedazo);
                $prefijo = "\\";
            }
            $path = str_finish($path, ".php");
            $bar->advance();
            $this->line("Loading details from {$table} table");
            $config = CrudGenerator::getModelDetailsFromDb($table);
            $config["modelo"] = $config["model"] = $modelName;
            $config["nameSpace"] = $nameSpace;
            $bar->advance();
            $this->info("Details loaded!");
            //$this->line(print_r($config, true));
            $bar->advance();
            $confirm = $this->choice("Do you wisth to continue and save the model to '{$path}' with the className '{$className}'?", ['yes', 'no'], 0);
            if ($confirm == 'yes') {
                $this->line("Saving Model for {$modelName} in {$path} with className '{$className}'");
                if (CrudGenerator::saveResource("sirgrimorum::templates.model", false, base_path($justPath), $fileName, $config)) {
                    $this->info("Model file saved!");
                    $bar->finish();
                } else {
                    $this->error("Something went wrong and the model file could not be saved");
                    $bar->finish();
                }
            } else {
                $bar->finish();
            }
        })->describe('Create a Model file based on a database table');

        Artisan::command('crudgen:createlang {model : The The NAME of the model} {--path= : Provide a custom paht for saving the model langs relatice to resource_path(), default is in vendor/crudgenerator (trans(crudgenerator::model))}', function ($model) {
            $this->line("Preparing model attributes");
            $bar = $this->output->createProgressBar(5);
            $options = $this->options('path');
            if ($options['path'] != "") {
                $path = $options['path'];
            } else {
                $path = "lang/vendor/crudgenerator/" . config("app.locale");
            }
            $path = str_replace("//", "/", str_replace(["\\", " "], ["/", ""], $path));
            $filename = str_finish(strtolower($model), ".php");
            $bar->advance();
            $this->line("Loading config array for {$model}");
            $config = CrudGenerator::getConfig($model, false);
            $bar->advance();
            $this->info("Config loaded!");
            //$this->line(print_r($config, true));
            $bar->advance();
            $this->line("Saving Lang file for {$model} in {$path} with filename '{$filename}'");
            if (CrudGenerator::saveResource("sirgrimorum::templates.lang", false, resource_path($path), $filename, $config)) {
                $this->info("Model Lang file saved!");
            } else {
                $this->error("Something went wrong and the model lang file could not be saved");
            }
            $bar->advance();
            $confirm = $this->choice("Do you wisth to create a Lang File for the model in es?", ['yes', 'no'], 0);
            if ($confirm == 'yes') {
                $path = "lang/vendor/crudgenerator/es";
                $filename = str_finish(strtolower($model), ".php");
                $this->line("Saving Lang file for {$model} in {$path} with filename '{$filename}'");
                if (CrudGenerator::saveResource("sirgrimorum::templates.langes", false, resource_path($path), $filename, $config)) {
                    $this->info("Model Lang file saved!");
                    $bar->finish();
                } else {
                    $this->error("Something went wrong and the model lang file could not be saved");
                    $bar->finish();
                }
            }
            $bar->finish();
        })->describe('Create a Model Lang file from config array');

        Artisan::command('crudgen:createconfig {model : The NAME of the model}{--merge : Try to merge with existing config file if it exist}{--path= : Provide a custom paht for saving the config file}', function ($model) {
            $this->line("Generating Config for {$model}");
            $bar = $this->output->createProgressBar(4);
            $options = $this->options('merge');
            if ($options['merge']) {
                $this->line("mergin");
                $config = CrudGenerator::getConfig($model, true, '', '', false);
            } else {
                $this->line("not mergin");
                $config = CrudGenerator::getConfig($model, false, '', '', false, true, true);
            }

            $bar->advance();
            $options = $this->options('path');
            if ($options['path'] != "") {
                $path = $options['path'];
            } else {
                $path = "sirgrimorum.models." . strtolower($model);
            }
            $this->info("Config generated!");
            //$this->line(print_r($config, true));
            $bar->advance();
            $confirm = $this->choice("Do you wisth to continue and save config to '{$path}'?", ['yes', 'no'], 0);
            if ($confirm == 'yes') {
                $this->line("Saving Config for {$model} in {$path}");
                if (CrudGenerator::saveConfig($config, $path)) {
                    $this->info("Config file saved!");
                    $bar->advance();
                    $confirm = $this->choice("Do you wisth to register the new config to the crudgenerator configuration file?", ['yes', 'no'], 0);
                    if ($confirm == 'yes') {
                        $this->line("Registering Config for {$model} in crudgenerator configuration file");
                        if (CrudGenerator::registerConfig($config, $path)) {
                            $this->info("Config registered!");
                            $bar->finish();
                        } else {
                            $this->error("Something went wrong and config could not be registered");
                            $bar->finish();
                        }
                    } else {
                        $bar->finish();
                    }
                } else {
                    $this->error("Something went wrong and the config file could not be saved");
                    $bar->finish();
                }
            } else {
                $bar->finish();
            }
        })->describe('Create a config file for a model');

        Artisan::command('crudgen:registermiddleware', function() {
            if (CrudGenerator::registerMiddleware()) {
                $this->info("CrudGenerator middleware registered");
            } else {
                $this->error("Something went wrong registering CrudGenerator middleware, please register ir in app/Http/Kernel.php");
            }
        })->describe('Register the CrudGenerator middleware in app/Http/Kernel.php');

        Artisan::command('crudgen:resources {model : The NAME of the model}', function ($model) {
            $bar = $this->output->createProgressBar(12);
            $confirm = $this->choice("Do you wisth to generate the files with Localized Routes?", ['yes', 'no'], 0);
            if ($confirm == "yes") {
                $localized = true;
            } else {
                $localized = false;
            }
            $config = CrudGenerator::getConfig($model, false);
            $confirm = $this->choice("Do you wisth to generate Controller, Request, Policy and Repository files?", ['yes', 'no'], 0);
            if ($confirm == 'yes') {
                $results = CrudGenerator::generateResources($config, $localized, $bar, "controller");
                if ($results[0]) {
                    $this->info("Controller file created");
                } else {
                    $this->error("Something went wrong saving Controller file");
                }
                if ($results[1]) {
                    $this->info("Request file created");
                } else {
                    $this->error("Something went wrong saving Request file");
                }
                if ($results[2]) {
                    $this->info("Policy file created");
                } else {
                    $this->error("Something went wrong saving Policy file");
                }
                if ($results[3]) {
                    $this->info("Repository file created");
                } else {
                    $this->error("Something went wrong saving Repository file");
                }
            }
            $confirm = $this->choice("Do you wisth to generate Create, Edit, Index and Show views?", ['yes', 'no'], 0);
            if ($confirm == 'yes') {
                $results = CrudGenerator::generateResources($config, $localized, $bar, "views");
                if ($results[0]) {
                    $this->info("Create view file created");
                } else {
                    $this->error("Something went wrong saving Create view file");
                }
                if ($results[1]) {
                    $this->info("Edit view file created");
                } else {
                    $this->error("Something went wrong saving Edit view file");
                }
                if ($results[2]) {
                    $this->info("Index view file created");
                } else {
                    $this->error("Something went wrong saving Index view file");
                }
                if ($results[3]) {
                    $this->info("Show view file created");
                } else {
                    $this->error("Something went wrong saving Show view file");
                }
            }
            $confirm = $this->choice("Do you wisth to append new routes for the model (web routes)?", ['yes', 'no'], 0);
            if ($confirm == 'yes') {
                if (CrudGenerator::registerRoutes($config, $localized)) {
                    $this->info("Routes registered");
                } else {
                    $this->error("Something went wrong registering routes");
                }
            }
            CrudGenerator::registerTransRoutes($config);
            $confirm = $this->choice("Do you wisth to register the model policy?", ['yes', 'no'], 0);
            if ($confirm == 'yes') {
                if (CrudGenerator::registerPolicy($config)) {
                    $this->info("Policy registered");
                } else {
                    $this->error("Something went wrong registering the policy");
                }
            }
            $bar->advance();
            $confirm = $this->choice("Do you wisth to create a Lang File for the model?", ['yes', 'no'], 0);
            if ($confirm == 'yes') {
                $path = "lang/vendor/crudgenerator/" . config("app.locale");
                $filename = str_finish(strtolower($model), ".php");
                $this->line("Saving Lang file for {$model} in {$path} with filename '{$filename}'");
                if (CrudGenerator::saveResource("sirgrimorum::templates.lang", $localized, resource_path($path), $filename, $config)) {
                    $this->info("Model Lang file saved!");
                } else {
                    $this->error("Something went wrong and the model lang file could not be saved");
                }
            }
            $bar->advance();
            $confirm = $this->choice("Do you wisth to create a Lang File for the model in es?", ['yes', 'no'], 0);
            if ($confirm == 'yes') {
                $path = "lang/vendor/crudgenerator/es";
                $filename = str_finish(strtolower($model), ".php");
                $this->line("Saving Lang file for {$model} in {$path} with filename '{$filename}'");
                if (CrudGenerator::saveResource("sirgrimorum::templates.langes", $localized, resource_path($path), $filename, $config)) {
                    $this->info("Model Lang file saved!");
                    $bar->finish();
                } else {
                    $this->error("Something went wrong and the model lang file could not be saved");
                    $bar->finish();
                }
            }
            $bar->finish();
        })->describe('Create a config file for a model');
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
