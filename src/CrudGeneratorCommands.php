<?php

namespace Sirgrimorum\CrudGenerator;

use Illuminate\Support\Str;

class CrudGeneratorCommands {

    protected $console;

    public function __construct($console) {
        $this->console = $console;
    }

    /**
     * Broadcast an alert to a user
     */
    public function sendalert() {
        $alertaClass = $this->console->ask("Notification Class?");
        if (class_exists($alertaClass)) {
            $email = $this->console->anticipate("User email?", ['andres.espinosa@grimorum.com']);
            if ($toUser = \App\User::where("email", "=", $email)->first()) {
                $message = $this->console->ask("Message?");
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
                  $this->console->line(print_r($result, true)); */
                $tiempo = $this->console->ask("Send in? (Just a number,next you will select seconds, minutes, etc.)");
                if (!is_int((int) $tiempo)) {
                    $tiempo = $this->console->ask("Send in? HAS TO BE AN INTEGER NUMBER");
                }
                if (is_int((int) $tiempo)) {
                    $unidad = $this->console->choice('...', ['seconds', 'minutes', 'hours', 'days'], 0);
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
                    $this->console->info("Listo!");
                } else {
                    $this->console->error("Paila!");
                }
            } else {
                $this->console->error("User not found");
            }
        } else {
            $this->console->error("Class not found");
        }
    }

    /**
     * Create a Model file based on a database table
     * @param string $table Table name
     */
    public function createmodel($table) {
        $this->console->line("Preparing model attributes");
        $bar = $this->console->output->createProgressBar(4);
        $options = $this->console->options('path');
        //$modelName = $singular = substr($table, 0, strlen($table) - 1);
        $modelName = $singular = Str::singular($table);
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
                $fileName = Str::finish(ucfirst($pedazo), ".php");
                $modelName = strtolower($pedazo);
            } else {
                $justPath .=$prefijoPath . $pedazo;
                $prefijoPath = "/";
            }
            $className .= $prefijo . ucfirst($pedazo);
            $prefijo = "\\";
        }
        $path = Str::finish($path, ".php");
        $bar->advance();
        $this->console->line("Loading details from {$table} table");
        $config = CrudGenerator::getModelDetailsFromDb($table);
        $config["modelo"] = $config["model"] = $modelName;
        $config["nameSpace"] = $nameSpace;
        $bar->advance();
        $this->console->info("Details loaded!");
        //$this->console->line(print_r($config, true));
        $bar->advance();
        $confirm = $this->console->choice("Do you wisth to continue and save the model to '{$path}' with the className '{$className}'?", ['yes', 'no'], 0);
        if ($confirm == 'yes') {
            $this->console->line("Saving Model for {$modelName} in {$path} with className '{$className}'");
            if (CrudGenerator::saveResource("sirgrimorum::templates.model", false, base_path($justPath), $fileName, $config)) {
                $this->console->info("Model file saved!");
                $bar->finish();
            } else {
                $this->console->error("Something went wrong and the model file could not be saved");
                $bar->finish();
            }
        } else {
            $bar->finish();
        }
    }

    /**
     * Create a Model Lang file from config array
     * @param string $model the model name in lowercase
     */
    public function createlang($model) {
        $this->console->line("Preparing model attributes");
        $bar = $this->console->output->createProgressBar((count(config("sirgrimorum.crudgenerator.list_locales"))+3));
        $options = $this->console->options('path');
        if ($options['path'] != "") {
            $path = $options['path'];
        } else {
            $path = "lang/vendor/crudgenerator/" . config("app.locale");
        }
        $path = str_replace("//", "/", str_replace(["\\", " "], ["/", ""], $path));
        $filename = Str::finish(strtolower($model), ".php");
        $bar->advance();
        $this->console->line("Loading config array for {$model}");
        $config = CrudGenerator::getConfig($model, false);
        $bar->advance();
        $this->console->info("Config loaded!");
        //$this->console->line(print_r($config, true));
        $bar->advance();
        $this->console->line("Saving Lang file for {$model} in {$path} with filename '{$filename}'");
        if (CrudGenerator::saveResource("sirgrimorum::templates.lang", false, resource_path($path), $filename, $config)) {
            $this->console->info("Model Lang file saved!");
        } else {
            $this->console->error("Something went wrong and the model lang file could not be saved");
        }
        $bar->advance();
        foreach (config("sirgrimorum.crudgenerator.list_locales") as $local){
            $this->console->line("for {$local}'");
            if (is_string($local)){
                if ($local != config("app.locale")){
                    $confirm = $this->console->choice("Do you wisth to create a Lang File for the model in {$local}?", ['yes', 'no'], 0);
                    if ($confirm == 'yes') {
                        $path = "lang/vendor/crudgenerator/{$local}";
                        $filename = Str::finish(strtolower($model), ".php");
                        $this->console->line("Saving Lang file for {$model} in {$path} with filename '{$filename}'");
                        if (CrudGenerator::saveResource("sirgrimorum::templates.langes", false, resource_path($path), $filename, $config)) {
                            $this->console->info("Model Lang file saved!");
                            $bar->advance();
                        } else {
                            $this->console->error("Something went wrong and the model lang file '{$filename}' could not be saved");
                            $bar->advance();
                        }
                    }
                }
            }
        }
        $bar->finish();
    }

    /**
     * Create a config file for a model
     * @param string $model the model name in lowercase
     */
    public function createconfig($model) {
        $this->console->line("Generating Config for {$model}");
        $bar = $this->console->output->createProgressBar(4);
        $options = $this->console->options('merge');
        if ($options['merge']) {
            $this->console->line("mergin");
            $config = CrudGenerator::getConfig($model, true, '', '', false);
        } else {
            $this->console->line("not mergin");
            $config = CrudGenerator::getConfig($model, false, '', '', false, true, true);
        }

        $bar->advance();
        $options = $this->console->options('path');
        if ($options['path'] != "") {
            $path = $options['path'];
        } else {
            $path = "sirgrimorum.models." . strtolower($model);
        }
        $this->console->info("Config generated!");
        //$this->console->line(print_r($config, true));
        $bar->advance();
        $confirm = $this->console->choice("Do you wisth to continue and save config to '{$path}'?", ['yes', 'no'], 0);
        if ($confirm == 'yes') {
            $this->console->line("Saving Config for {$model} in {$path}");
            if (CrudGenerator::saveConfig($config, $path)) {
                $this->console->info("Config file saved!");
                $bar->advance();
                $confirm = $this->console->choice("Do you wisth to register the new config to the crudgenerator configuration file?", ['yes', 'no'], 0);
                if ($confirm == 'yes') {
                    $this->console->line("Registering Config for {$model} in crudgenerator configuration file");
                    if (CrudGenerator::registerConfig($config, $path)) {
                        $this->console->info("Config registered!");
                        $bar->finish();
                    } else {
                        $this->console->error("Something went wrong and config could not be registered");
                        $bar->finish();
                    }
                } else {
                    $bar->finish();
                }
            } else {
                $this->console->error("Something went wrong and the config file could not be saved");
                $bar->finish();
            }
        } else {
            $bar->finish();
        }
    }

    /**
     * Register the CrudGenerator middleware in app/Http/Kernel.php
     */
    public function registermiddleware() {
        if (CrudGenerator::registerMiddleware()) {
            $this->console->info("CrudGenerator middleware registered");
        } else {
            $this->console->error("Something went wrong registering CrudGenerator middleware, please register ir in app/Http/Kernel.php");
        }
    }

    /**
     * Create a config file for a model
     * @param string $model the model name in lowercase
     */
    public function resources($console,$model) {
        $this->console = $console;
        $bar = $this->console->output->createProgressBar(12);
        $confirm = $this->console->choice("Do you wisth to generate the files with Localized Routes?", ['yes', 'no'], 0);
        if ($confirm == "yes") {
            $localized = true;
        } else {
            $localized = false;
        }
        $config = CrudGenerator::getConfig($model, false);
        $confirm = $this->console->choice("Do you wisth to generate Controller, Request, Policy and Repository files?", ['yes', 'no'], 0);
        if ($confirm == 'yes') {
            $results = CrudGenerator::generateResources($config, $localized, $bar, "controller");
            if ($results[0]) {
                $this->console->info("Controller file created");
            } else {
                $this->console->error("Something went wrong saving Controller file");
            }
            if ($results[1]) {
                $this->console->info("Request file created");
            } else {
                $this->console->error("Something went wrong saving Request file");
            }
            if ($results[2]) {
                $this->console->info("Policy file created");
            } else {
                $this->console->error("Something went wrong saving Policy file");
            }
            if ($results[3]) {
                $this->console->info("Repository file created");
            } else {
                $this->console->error("Something went wrong saving Repository file");
            }
        }
        $confirm = $this->console->choice("Do you wisth to generate Create, Edit, Index and Show views?", ['yes', 'no'], 0);
        if ($confirm == 'yes') {
            $results = CrudGenerator::generateResources($config, $localized, $bar, "views");
            if ($results[0]) {
                $this->console->info("Create view file created");
            } else {
                $this->console->error("Something went wrong saving Create view file");
            }
            if ($results[1]) {
                $this->console->info("Edit view file created");
            } else {
                $this->console->error("Something went wrong saving Edit view file");
            }
            if ($results[2]) {
                $this->console->info("Index view file created");
            } else {
                $this->console->error("Something went wrong saving Index view file");
            }
            if ($results[3]) {
                $this->console->info("Show view file created");
            } else {
                $this->console->error("Something went wrong saving Show view file");
            }
        }
        $confirm = $this->console->choice("Do you wisth to append new routes for the model (web routes)?", ['yes', 'no'], 0);
        if ($confirm == 'yes') {
            if (CrudGenerator::registerRoutes($config, $localized)) {
                $this->console->info("Routes registered");
            } else {
                $this->console->error("Something went wrong registering routes");
            }
        }
        CrudGenerator::registerTransRoutes($config);
        $confirm = $this->console->choice("Do you wisth to register the model policy?", ['yes', 'no'], 0);
        if ($confirm == 'yes') {
            if (CrudGenerator::registerPolicy($config)) {
                $this->console->info("Policy registered");
            } else {
                $this->console->error("Something went wrong registering the policy");
            }
        }
        $bar->advance();
        $confirm = $this->console->choice("Do you wisth to create a Lang File for the model?", ['yes', 'no'], 0);
        if ($confirm == 'yes') {
            $path = "lang/vendor/crudgenerator/" . config("app.locale");
            $filename = Str::finish(strtolower($model), ".php");
            $this->console->line("Saving Lang file for {$model} in {$path} with filename '{$filename}'");
            if (CrudGenerator::saveResource("sirgrimorum::templates.lang", $localized, resource_path($path), $filename, $config)) {
                $this->console->info("Model Lang file saved!");
            } else {
                $this->console->error("Something went wrong and the model lang file could not be saved");
            }
        }
        $bar->advance();
        $confirm = $this->console->choice("Do you wisth to create a Lang File for the model in es?", ['yes', 'no'], 0);
        if ($confirm == 'yes') {
            $path = "lang/vendor/crudgenerator/es";
            $filename = Str::finish(strtolower($model), ".php");
            $this->console->line("Saving Lang file for {$model} in {$path} with filename '{$filename}'");
            if (CrudGenerator::saveResource("sirgrimorum::templates.langes", $localized, resource_path($path), $filename, $config)) {
                $this->console->info("Model Lang file saved!");
                $bar->finish();
            } else {
                $this->console->error("Something went wrong and the model lang file could not be saved");
                $bar->finish();
            }
        }
        $bar->finish();
    }

}
