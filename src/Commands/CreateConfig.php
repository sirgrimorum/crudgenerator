<?php

namespace Sirgrimorum\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Sirgrimorum\CrudGenerator\CrudGenerator;

class CreateConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crudgen:createconfig {model : The NAME of the model}{--merge : Try to merge with existing config file if it exist}{--path= : Provide a custom paht for saving the config file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a config file for a model';

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
        $model = $this->argument('model');
        $this->line("Generating Config for {$model}");
        $bar = $this->output->createProgressBar(4);
        $options = $this->options();
        if ($options['merge']) {
            $this->line("mergin");
            $config = CrudGenerator::getConfig($model, true, '', '', false);
        } else {
            $this->line("not mergin");
            $config = CrudGenerator::getConfig($model, false, '', '', false, true, true);
        }

        $bar->advance();
        //$options = $this->options();
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
    }
}
