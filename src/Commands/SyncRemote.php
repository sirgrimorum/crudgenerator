<?php

namespace Sirgrimorum\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Symfony\Component\Process\Process;

class SyncRemote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crudgen:syncremote ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync remote BD in image of local BD';

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

        if (App::environment('production')) {
            $this->error("Please don't try and run this in production... will not end well.");
            return;
        }
        if (!$dump_local_dir) {
            $this->error('You need to add environment variables! (SYNC_DB_LOCAL_DUMP_DIR)');
            return;
        }
        if (!$local_db_user) {
            $this->error('You need to add environment variables! (DB_USERNAME)');
            return;
        }
        if (!$remote_db_user) {
            $this->error('You need to add environment variables! (SYNC_DB_REMOTE_DB_USER)');
            return;
        }
        if (!$remote_url) {
            $this->error('You need to add environment variables! (SYNC_DB_REMOTE_URL)');
            return;
        }
        if (!$remote_db) {
            $this->error('You need to add environment variables! (SYNC_DB_REMOTE_DB_NAME)');
            return;
        }
        if (!$local_db) {
            $this->error('You need to add environment variables! (DB_DATABASE)');
            return;
        }
        if (!$local_mysql_path) {
            $this->error('You need to add environment variables! (SYNC_DB_LOCAL_MYSQL_PATH)');
            return;
        }
        $dump_local_dir = \Illuminate\Support\Str::finish($dump_local_dir, "/");
        $local_mysql_path = \Illuminate\Support\Str::finish($local_mysql_path, "/");

        $bar = $this->output->createProgressBar(6);
        $bar->start();

        $confirm = $this->choice("Are you sure? This will erase all data in remote BD", ['yes', 'no'], 1);
        if ($confirm == 'yes') {
            $generarCopia = true;
            if (file_exists("{$dump_local_dir}sync_dump.sql")) {
                $confirm = $this->choice("There is a copy of the DB sync_dump.sql. Do you wish to keep it or replace it?", ['keep', 'replace'], 1);
                if ($confirm == 'replace') {
                    $this->info("Remove local backup...");
                    $process = new Process("rm {$dump_local_dir}sync_dump.sql -f");
                    $process->run();
                    if (!$process->isSuccessful()) {
                        $bar->finish();
                        return;
                    }
                } else {
                    $generarCopia = false;
                }
            }
            if ($generarCopia) {
                $this->info("Generating backup of local db in local...");
                $bar->advance();
                if (!$local_db_pass) {
                    $process = new Process("{$local_mysql_path}mysqldump -v -h localhost -u $local_db_user $local_db > {$dump_local_dir}sync_dump.sql");
                } else {
                    $process = new Process("{$local_mysql_path}mysqldump -v -h localhost -u $local_db_user -p$local_db_pass $local_db > {$dump_local_dir}sync_dump.sql");
                }
                $process->run();
                if (!$process->isSuccessful()) {
                    $bar->finish();
                    return;
                }
            } else {
            }

            $this->info("Delete remote DB...");
            $bar->advance();
            if (!$remote_db_pass) {
                $process = new Process("{$local_mysql_path}mysqladmin -h $remote_url -u $remote_db_user drop $remote_db -f || true");
            } else {
                $process = new Process("{$local_mysql_path}mysqladmin -h $remote_url -u $remote_db_user -p$remote_db_pass drop $remote_db -f || true");
            }
            $process->run();
            if (!$process->isSuccessful()) {
                $bar->finish();
                return;
            }

            $this->info("Create remote DB...");
            $bar->advance();
            if (!$remote_db_pass) {
                $process = new Process("{$local_mysql_path}mysqladmin -h $remote_url -u $remote_db_user create $remote_db");
            } else {
                $process = new Process("{$local_mysql_path}mysqladmin -h $remote_url -u $remote_db_user -p$remote_db_pass create $remote_db");
            }
            $process->run();
            if (!$process->isSuccessful()) {
                $bar->finish();
                return;
            }

            $this->info('Migrating...');
            $bar->advance();
            if (!$remote_db_pass) {
                exec("{$local_mysql_path}mysql -h $remote_url -u $remote_db_user $remote_db < {$dump_local_dir}sync_dump.sql");
            } else {
                exec("{$local_mysql_path}mysql -h $remote_url -u $remote_db_user -p$remote_db_pass $remote_db < {$dump_local_dir}sync_dump.sql");
            }

            $confirm = $this->choice("Do you wish to remove the local backup or keep it?", ['remove', 'keep'], 0);
            if ($confirm == 'remove') {
                $this->info('Removing back up files.');
                $bar->advance();
                exec("rm {$dump_local_dir}sync_dump.sql -f");
            } else {
                $bar->advance();
            }

            $this->info('Complete! Remote DB is synced with you.');
            $bar->finish();
        } else {
            $bar->finish();
        }
        return 0;
    }
}
