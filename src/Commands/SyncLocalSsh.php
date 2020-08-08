<?php

namespace Sirgrimorum\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use phpseclib\Net\SSH2;
use phpseclib\Net\SFTP;

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

        // Checking to make sure this isn't production.
        if (App::environment('production')) {
            $this->error("Please don't try and run this in production... will not end well.");
            return;
        }
        if (!$dump_remote_dir || !$remote_db_user || !$remote_db_pass || !$remote_url || !$remote_db || !$ssh_user || !$ssh_pass) {
            $this->error('You need to add environment variables! (SYNC_DB_REMOTE_URL, SYNC_DB_REMOTE_DB_NAME, SYNC_DB_REMOTE_DB_USER, SYNC_DB_REMOTE_DB_PASS, SYNC_DB_REMOTE_SSH_USERNAME, SYNC_DB_REMOTE_SSH_PASSWORD, SYNC_DB_REMOTE_DUMP_DIR)');
            return;
        }
        $dump_remote_dir = \Illuminate\Support\Str::finish($dump_remote_dir, "/");

        $bar = $this->output->createProgressBar(7);

        $confirm = $this->choice("Are you sure? This will erase all data in local BD", ['yes', 'no'], 1);
        if ($confirm == 'yes') {
            $this->line("Connecting to remote ssh...");
            $bar->advance();
            // Connect via ssh to dump the db on the remote server.
            $ssh = new SSH2($remote_url);
            if (!$ssh->login($ssh_user, $ssh_pass)) {
                $this->error('Login failed make sure your ssh username and password is set in your env file.');
                $bar->finish();
                return;
            }
            $this->line("Generating backup in remote...");
            $bar->advance();
            $ssh->exec('sudo mysqldump -v -h localhost -u ' . $remote_db_user . '  -p' . $remote_db_pass . ' ' . $remote_db . ' > ' . $dump_remote_dir . 'sync_dump.sql');

            $this->line("Downloading backup from remote...");
            $bar->advance();
            // Connect via sftp to d/l the dump
            $sftp = new SFTP($remote_url);

            if (!$sftp->login($ssh_user, $ssh_pass)) {
                $this->error('Login in sftp failed make sure your SSH username and password is set in your env file.');
                return;
            }

            // Temporarily remove memory limit
            ini_set('memory_limit', '-1');
            $sftp->get($dump_remote_dir . 'sync_dump.sql', storage_path('sync_dump.sql'));

            $this->line("Importing data to local...");
            $bar->advance();

            DB::unprepared(File::get(storage_path('sync_dump.sql')));

            $this->line('Migrating...');
            $bar->advance();
            $this->call('migrate');

            $this->line('Removing back up files.');
            $bar->advance();
            $ssh->exec('rm sync_dump.sql');
            File::delete(storage_path('sync_dump.sql'));

            $this->line('Complete! You are synced with the remote DB.');
            $bar->finish();
        } else {
            $bar->finish();
        }
    }
}
