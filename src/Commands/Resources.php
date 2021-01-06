<?php

namespace Sirgrimorum\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use Illuminate\Support\Str;

class Resources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crudgen:resources {model : The NAME of the model}';

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
        $bar = $this->output->createProgressBar(12);
        $bar->start();
        
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
            $filename = Str::finish(strtolower($model), ".php");
            $this->info("Saving Lang file for {$model} in {$path} with filename '{$filename}'");
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
            $filename = Str::finish(strtolower($model), ".php");
            $this->info("Saving Lang file for {$model} in {$path} with filename '{$filename}'");
            if (CrudGenerator::saveResource("sirgrimorum::templates.langes", $localized, resource_path($path), $filename, $config)) {
                $this->info("Model Lang file saved!");
                $bar->finish();
            } else {
                $this->error("Something went wrong and the model lang file could not be saved");
                $bar->finish();
            }
        }
        $bar->finish();
        return 0;
    }
}
