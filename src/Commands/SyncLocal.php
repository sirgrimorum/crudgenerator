<?php

namespace Sirgrimorum\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class SyncLocal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crudgen:synclocaldb:ssh ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync local DB in image of remote DB using ssh';

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
        $remote_url = env('SYNC_DB_REMOTE_URL');
        $remote_db = env('SYNC_DB_REMOTE_DB_NAME');
        $remote_db_user = env('SYNC_DB_REMOTE_DB_USER');
        $remote_db_pass = env('SYNC_DB_REMOTE_DB_PASS');
        $local_db = env('DB_DATABASE');
        $local_db_user = env('DB_USERNAME');
        $local_db_pass = env('DB_PASSWORD');
        $local_mysql_path = env('SYNC_DB_LOCAL_MYSQL_PATH');
        $dump_local_dir = env('SYNC_DB_LOCAL_DUMP_DIR');

        // Checking to make sure this isn't production.
        if (App::environment('production')) {
            $this->error("Please don't try and run this in production... will not end well.");
            return;
        }
        if (!$dump_local_dir || !$local_db_user || !$local_db_pass || !$remote_db_user || !$remote_db_pass || !$remote_url || !$remote_db || !$local_db || !$local_mysql_path) {
            $this->error('You need to add environment variables! (DB_DATABASE, DB_USERNAME, DB_PASSWORD, SYNC_DB_REMOTE_URL, SYNC_DB_REMOTE_DB_NAME, SYNC_DB_REMOTE_DB_USER, SYNC_DB_REMOTE_DB_PASS, SYNC_DB_LOCAL_MYSQL_PATH, SYNC_DB_LOCAL_DUMP_DIR)');
            return;
        }
        $dump_local_dir = \Illuminate\Support\Str::finish($dump_local_dir, "/");
        $local_mysql_path = \Illuminate\Support\Str::finish($local_mysql_path, "/");

        $bar = $this->output->createProgressBar(7);

        $confirm = $this->choice("Are you sure? This will erase all data in local BD", ['yes', 'no'], 1);
        if ($confirm == 'yes') {
            $this->line("Accessing local MySQL dir...");
            $bar->advance();
            // Go to Mysql path.
            $this->exec("cd $local_mysql_path");
            $this->line("Generating backup of remote in local...");
            $bar->advance();
            $this->exec("mysqldump -v -h $remote_url -u $remote_db_user -p$remote_db_pass $remote_db > {$dump_local_dir}sync_dump.sql");

            $this->line("Delete local DB...");
            $bar->advance();
            $this->exec("mysqladmin -h localhost -u $local_db_user -p$local_db_pass drop $local_db");

            $this->line("Create local DB...");
            $bar->advance();
            $this->exec("mysqladmin -h localhost -u $local_db_user -p$local_db_pass create $local_db");

            $this->line('Migrating...');
            $bar->advance();
            $this->exec("mysql -h localhost -u $local_db_user -p$local_db_pass $local_db < {$dump_local_dir}sync_dump.sql");

            $this->line('Removing back up files.');
            $bar->advance();
            $this->exec("rm {$dump_local_dir}sync_dump.sql");

            $this->line('Complete! You are synced with the remote DB.');
            $bar->finish();
        } else {
            $bar->finish();
        }
    }
}
