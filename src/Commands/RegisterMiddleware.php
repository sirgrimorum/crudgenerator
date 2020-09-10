<?php

namespace Sirgrimorum\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Sirgrimorum\CrudGenerator\CrudGenerator;

class RegisterMiddleware extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crudgen:registermiddleware';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register the CrudGenerator middleware in app/Http/Kernel.php';

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
        if (CrudGenerator::registerMiddleware()) {
            $this->info("CrudGenerator middleware registered");
        } else {
            $this->error("Something went wrong registering CrudGenerator middleware, please register ir in app/Http/Kernel.php");
        }
        return 0;
    }
}
