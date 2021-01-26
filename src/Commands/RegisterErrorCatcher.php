<?php

namespace Sirgrimorum\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Sirgrimorum\CrudGenerator\CrudGenerator;

class RegisterErrorCatcher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crudgen:registererrorcatcher';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register the CrudGenerator ErrorCatcher in app/Exceptions/Handler.php';

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
        if (CrudGenerator::registerErrorCatcher()) {
            $confirm = $this->choice("Do you wish to register the error catching config to the crudgenerator configuration file?", ['yes', 'no'], 0);
            if ($confirm == 'yes') {
                $this->info("Registering Config for catched errors in crudgenerator configuration file");
                if (CrudGenerator::registerConfigSimple("CatchedErrors", "sirgrimorum.models.catchedError", config_path("sirgrimorum.models.catchedError"))) {
                    $this->info("Config registered!");
                } else {
                    $this->error("Something went wrong and config could not be registered");
                }
            }
            $this->info("CrudGenerator ErrorCatcher registered");
        } else {
            $this->error("Something went wrong registering CrudGenerator ErrorCatcher, please register it in app/Exceptions/Handler.php");
        }
        return 0;
    }
}
