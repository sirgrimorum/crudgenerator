<?php

namespace Sirgrimorum\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Sirgrimorum\CrudGenerator\CrudGenerator;

class CreateLang extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crudgen:createlang {model : The The NAME of the model} {--path= : Provide a custom paht for saving the model langs relative to resource_path(), default is in vendor/crudgenerator (trans(crudgenerator::model))}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Model Lang file from config array';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Preparing model attributes");
        $model = $this->argument('model');
        $bar = $this->output->createProgressBar((count(config("sirgrimorum.crudgenerator.list_locales")) + 3));
        $bar->start();
        
        $options = $this->options();
        if ($options['path'] != "") {
            $path = $options['path'];
        } else {
            $path = "lang/vendor/crudgenerator/" . config("app.locale");
        }
        $path = str_replace("//", "/", str_replace(["\\", " "], ["/", ""], $path));
        $filename = \Illuminate\Support\Str::finish(strtolower($model), ".php");
        $bar->advance();
        $this->info("Loading config array for {$model}");
        $config = CrudGenerator::getConfig($model, false);
        $bar->advance();
        $this->info("Config loaded!");
        //$this->info(print_r($config, true));
        $bar->advance();
        $this->info("Saving Lang file for {$model} in {$path} with filename '{$filename}'");
        if (CrudGenerator::saveResource("sirgrimorum::templates.lang", false, resource_path($path), $filename, $config)) {
            $this->info("Model Lang file saved!");
        } else {
            $this->error("Something went wrong and the model lang file could not be saved");
        }
        $bar->advance();
        foreach (config("sirgrimorum.crudgenerator.list_locales") as $local) {
            $this->info("for {$local}'");
            if (is_string($local)) {
                if ($local != config("app.locale")) {
                    $confirm = $this->choice("Do you wisth to create a Lang File for the model in {$local}?", ['yes', 'no'], 0);
                    if ($confirm == 'yes') {
                        $path = "lang/vendor/crudgenerator/{$local}";
                        $filename = \Illuminate\Support\Str::finish(strtolower($model), ".php");
                        $this->info("Saving Lang file for {$model} in {$path} with filename '{$filename}'");
                        if (CrudGenerator::saveResource("sirgrimorum::templates.langes", false, resource_path($path), $filename, $config)) {
                            $this->info("Model Lang file saved!");
                            $bar->advance();
                        } else {
                            $this->error("Something went wrong and the model lang file '{$filename}' could not be saved");
                            $bar->advance();
                        }
                    }
                }
            }
        }
        $bar->finish();
    }
}
