<?php

namespace Sirgrimorum\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use Illuminate\Support\Str;

class CreateModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crudgen:createmodel {table : The Table name} {--path= : Provide a custom path for saving the model file relative to base_path()}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Model file based on a database table';

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
        $bar = $this->output->createProgressBar(4);
        $bar->start();
        
        $table = $this->argument('table');
        $options = $this->options();
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
                $justPath .= $prefijoPath . $pedazo;
                $prefijoPath = "/";
            }
            $className .= $prefijo . ucfirst($pedazo);
            $prefijo = "\\";
        }
        $path = Str::finish($path, ".php");
        $bar->advance();
        $this->info("Loading details from {$table} table");
        $config = CrudGenerator::getModelDetailsFromDb($table);
        $config["modelo"] = $config["model"] = $modelName;
        $config["nameSpace"] = $nameSpace;
        $bar->advance();
        $this->info("Details loaded!");
        //$this->info(print_r($config, true));
        $bar->advance();
        $confirm = $this->choice("Do you wisth to continue and save the model to '{$path}' with the className '{$className}'?", ['yes', 'no'], 0);
        if ($confirm == 'yes') {
            $this->info("Saving Model for {$modelName} in {$path} with className '{$className}'");
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
        return 0;
    }
}
