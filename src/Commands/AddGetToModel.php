<?php

namespace Sirgrimorum\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Sirgrimorum\CrudGenerator\CrudGenerator;

class AddGetToModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crudgen:addgettomodel {model : The NAME of the model} {--path= : Provide a custom path for the model file relative to base_path()}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add the get function to a Model File';

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
        $modelName = $this->argument('model');
        $this->info("Loading {$modelName} file");
        $bar = $this->output->createProgressBar(3);
        $bar->start();

        $options = $this->options();

        if ($options['path'] != "") {
            $path = $options['path'];
        } else {
            $path = "app/" . ucfirst(strtolower($modelName));
        }
        $path = str_replace("//", "/", str_replace(["\\", " "], ["/", ""], $path));
        $AuxclassName = explode("/", str_replace(".php", "", $path));
        $className = "";
        $justPath = "";
        $prefijoPath = "/";
        $prefijo = "";
        $fileName = "";
        foreach ($AuxclassName as $indice => $pedazo) {
            if ($indice == count($AuxclassName) - 1) {
                $fileName = \Illuminate\Support\Str::finish(ucfirst($pedazo), ".php");
                $modelName = strtolower($pedazo);
            } else {
                $justPath .= $prefijoPath . $pedazo;
                $prefijoPath = "/";
            }
            $className .= $prefijo . ucfirst($pedazo);
            $prefijo = "\\";
        }
        $path = \Illuminate\Support\Str::finish($path, ".php");
        $bar->advance();
        $this->info("File loaded!");
        //$this->info(print_r($config, true));
        $confirm = $this->choice("Do you wisth to continue and edit model file in '{$path}' with the className '{$className}'?", ['yes', 'no'], 0);
        if ($confirm == 'yes') {
            $this->info("Saving Model for {$modelName} in {$path} with className '{$className}'");
            if (CrudGenerator::addGetToModel(base_path($justPath), $fileName)) {
                $this->info("Model file saved!");
                $bar->finish();
            } else {
                $this->error("Something went wrong and the model file could not be saved");
                $bar->finish();
            }
        } else {
            $bar->finish();
        }
        return 0;
    }
}
