<?php

namespace Sirgrimorum\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use phpseclib\Net\SSH2;
use phpseclib\Net\SFTP;

class SyncRemoteSsh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crudgen:syncremote:ssh ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync remote BD in image of local BD using ssh';

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
        $ssh_user = env('SYNC_DB_REMOTE_SSH_USERNAME');
        $ssh_pass = env('SYNC_DB_REMOTE_SSH_PASSWORD');
        $dump_local_dir = env('SYNC_DB_LOCAL_DUMP_DIR');
        $dump_remote_dir = env('SYNC_DB_REMOTE_DUMP_DIR');

        if (!$ssh_user || !$ssh_pass || !$dump_local_dir || !$local_db_user || !$local_db_pass || !$remote_db_user || !$remote_db_pass || !$remote_url || !$remote_db || !$local_db || !$local_mysql_path) {
            $this->error('You need to add environment variables! (SYNC_DB_REMOTE_SSH_USERNAME, SYNC_DB_REMOTE_SSH_PASSWORD, DB_DATABASE, DB_USERNAME, DB_PASSWORD, SYNC_DB_REMOTE_URL, SYNC_DB_REMOTE_DB_NAME, SYNC_DB_REMOTE_DB_USER, SYNC_DB_REMOTE_DB_PASS, SYNC_DB_LOCAL_MYSQL_PATH, SYNC_DB_LOCAL_DUMP_DIR)');
            return;
        }
        $dump_local_dir = \Illuminate\Support\Str::finish($dump_local_dir, "/");
        $dump_remote_dir = \Illuminate\Support\Str::finish($dump_remote_dir, "/");
        $local_mysql_path = \Illuminate\Support\Str::finish($local_mysql_path, "/");

        $bar = $this->output->createProgressBar(9);

        $confirm = $this->choice("Are you sure? This will erase all data in remote BD", ['yes', 'no'], 1);
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
            // Connect via sftp to d/l the dump
            $sftp = new SFTP($remote_url);
            if (!$sftp->login($ssh_user, $ssh_pass)) {
                $this->error('Login in sftp failed make sure your SSH username and password is set in your env file.');
                return;
            }

            $this->line("Accessing local MySQL dir...");
            $bar->advance();
            // Go to Mysql path.
            $this->exec("cd $local_mysql_path");
            $this->line("Generating backup of local db in local...");
            $bar->advance();
            $this->exec("mysqldump -v -h localhost -u $local_db_user -p$local_db_pass $local_db > {$dump_local_dir}sync_dump.sql");

            $this->line("Uploading backup to remote...");
            $bar->advance();
            // Temporarily remove memory limit
            ini_set('memory_limit', '-1');
            $sftp->put("{$dump_remote_dir}sync_dump.sql", File::get("{$dump_local_dir}sync_dump.sql"));

            $this->line("Delete remote DB...");
            $bar->advance();
            $ssh->exec("mysqladmin -h localhost -u $remote_db_user -p$remote_db_pass drop $remote_db");

            $this->line("Create remote DB...");
            $bar->advance();
            $ssh->exec("mysqladmin -h localhost -u $remote_db_user -p$remote_db_pass create $remote_db");

            $this->line('Migrating...');
            $bar->advance();
            $ssh->exec("mysql -h localhost -u $remote_db_user -p$remote_db_pass $remote_db < {$dump_remote_dir}sync_dump.sql");

            $this->line('Removing back up files.');
            $bar->advance();
            $ssh->exec("rm {$dump_remote_dir}sync_dump.sql");

            $this->line('Complete! Remote DB is synced with you.');
            $bar->finish();
        } else {
            $bar->finish();
        }
    }
}
