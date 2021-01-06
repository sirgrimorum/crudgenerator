<?php

namespace Sirgrimorum\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use phpseclib\Net\SSH2;
use phpseclib\Net\SFTP;
use Symfony\Component\Process\Process;

class SyncLocalSsh extends Command
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
        $ssh_user = env('SYNC_DB_REMOTE_SSH_USERNAME');
        $ssh_pass = env('SYNC_DB_REMOTE_SSH_PASSWORD');
        $dump_remote_dir = env('SYNC_DB_REMOTE_DUMP_DIR');
        $local_db = env('DB_DATABASE');
        $local_db_user = env('DB_USERNAME');
        $local_db_pass = env('DB_PASSWORD');
        $local_mysql_path = env('SYNC_DB_LOCAL_MYSQL_PATH');

        // Checking to make sure this isn't production.
        if (App::environment('production')) {
            $this->error("Please don't try and run this in production... will not end well.");
            return;
        }
        if (!$local_db_user) {
            $this->error('You need to add environment variables! (DB_USERNAME)');
            return;
        }
        if (!$dump_remote_dir) {
            $this->error('You need to add environment variables! (SYNC_DB_REMOTE_DUMP_DIR)');
            return;
        }
        if (!$ssh_user) {
            $this->error('You need to add environment variables! (SYNC_DB_REMOTE_SSH_USERNAME)');
            return;
        }
        if (!$ssh_pass) {
            $this->error('You need to add environment variables! (SYNC_DB_REMOTE_SSH_PASSWORD)');
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
        $dump_remote_dir = Str::finish($dump_remote_dir, "/");
        $local_mysql_path = Str::finish($local_mysql_path, "/");

        $bar = $this->output->createProgressBar(9);
        $bar->start();

        $confirm = $this->choice("Are you sure? This will erase all data in local BD", ['yes', 'no'], 1);
        if ($confirm == 'yes') {
            $this->info("Connecting to remote ssh...");
            $bar->advance();
            // Connect via ssh to dump the db on the remote server.
            $ssh = new SSH2($remote_url);
            if (!$ssh->login($ssh_user, $ssh_pass)) {
                $this->error('Login failed make sure your ssh username and password is set in your env file.');
                $bar->finish();
                return;
            }
            $generarCopia = true;
            if (Storage::exists('sync_dump.sql')) {
                $confirm = $this->choice("There is a copy of the DB sync_dump.sql. Do you wish to keep it or replace it?", ['keep', 'replace'], 1);
                if ($confirm == 'replace') {
                    $this->info("Remove backup...");
                    $ssh->exec('rm sync_dump.sql -f');
                    File::delete(storage_path('sync_dump.sql'));
                } else {
                    $generarCopia = false;
                }
            }
            if ($generarCopia) {
                $this->info("Generating backup in remote...");
                $ssh->exec('rm sync_dump.sql -f');
                $bar->advance();
                if (!$remote_db_pass) {
                    $ssh->exec('sudo mysqldump -v -h localhost -u ' . $remote_db_user . ' ' . $remote_db . ' > ' . $dump_remote_dir . 'sync_dump.sql');
                } else {
                    $ssh->exec('sudo mysqldump -v -h localhost -u ' . $remote_db_user . '  -p' . $remote_db_pass . ' ' . $remote_db . ' > ' . $dump_remote_dir . 'sync_dump.sql');
                }

                $this->info("Downloading backup from remote...");
                $bar->advance();
                // Connect via sftp to d/l the dump
                $sftp = new SFTP($remote_url);

                if (!$sftp->login($ssh_user, $ssh_pass)) {
                    $this->error('Login in sftp failed make sure your SSH username and password is set in your env file.');
                    return;
                }
            } else {
                $bar->advance();
                $bar->advance();
            }

            $this->info("Delete local DB...");
            $bar->advance();
            if (!$local_db_pass) {
                $process = new Process(["{$local_mysql_path}mysqladmin", "-h localhost", "-u $local_db_user", "drop $local_db", "-f || true"]);
            } else {
                $process = new Process(["{$local_mysql_path}mysqladmin", "-h localhost", "-u $local_db_user", "-p$local_db_pass", "drop $local_db", "-f || true"]);
            }
            $process->run();
            if (!$process->isSuccessful()) {
                $bar->finish();
                return;
            }
            $this->info("Create local DB...");
            $bar->advance();
            if (!$local_db_pass) {
                $process = new Process(["{$local_mysql_path}mysqladmin", "-h localhost", "-u $local_db_user", "create $local_db"]);
            } else {
                $process = new Process(["{$local_mysql_path}mysqladmin", "-h localhost", "-u $local_db_user", "-p$local_db_pass", "create $local_db"]);
            }
            $process->run();
            if (!$process->isSuccessful()) {
                $bar->finish();
                return;
            }

            // Temporarily remove memory limit
            ini_set('memory_limit', '-1');
            $sftp->get($dump_remote_dir . 'sync_dump.sql', storage_path('sync_dump.sql'));

            $this->info("Importing data to local...");
            $bar->advance();

            DB::unprepared(File::get(storage_path('sync_dump.sql')));

            $this->info('Migrating...');
            $bar->advance();
            $this->call('migrate');

            $confirm = $this->choice("Do you wish to remove the backup or keep it?", ['remove', 'keep'], 0);
            if ($confirm == 'remove') {
                $this->info('Removing back up files.');
                $bar->advance();
                $ssh->exec('rm sync_dump.sql -f');
                File::delete(storage_path('sync_dump.sql'));
            } else {
                $bar->advance();
            }

            $this->info('Complete! You are synced with the remote DB.');
            $bar->finish();
        } else {
            $bar->finish();
        }
        return 0;
    }
}
