<?php

namespace Sirgrimorum\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use phpseclib\Net\SSH2;
use phpseclib\Net\SFTP;
use Symfony\Component\Process\Process;

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

        if (!$ssh_user) {
            $this->error('You need to add environment variables! (SYNC_DB_REMOTE_SSH_USERNAME)');
            return;
        }
        if (!$ssh_pass) {
            $this->error('You need to add environment variables! (SYNC_DB_REMOTE_SSH_PASSWORD)');
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
        $dump_remote_dir = \Illuminate\Support\Str::finish($dump_remote_dir, "/");
        $local_mysql_path = \Illuminate\Support\Str::finish($local_mysql_path, "/");

        $bar = $this->output->createProgressBar(8);
        $bar->start();

        $confirm = $this->choice("Are you sure? This will erase all data in remote BD", ['yes', 'no'], 1);
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
            // Connect via sftp to d/l the dump
            $sftp = new SFTP($remote_url);
            if (!$sftp->login($ssh_user, $ssh_pass)) {
                $this->error('Login in sftp failed make sure your SSH username and password is set in your env file.');
                return;
            }

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
                $bar->advance();
            }

            $this->info("Uploading backup to remote...");
            $bar->advance();
            // Temporarily remove memory limit
            ini_set('memory_limit', '-1');
            $sftp->put("{$dump_remote_dir}sync_dump.sql", File::get("{$dump_local_dir}sync_dump.sql"));

            $this->info("Delete remote DB...");
            $bar->advance();
            if (!$remote_db_pass) {
                $ssh->exec("mysqladmin -h localhost -u $remote_db_user drop $remote_db -f || true");
            } else {
                $ssh->exec("mysqladmin -h localhost -u $remote_db_user -p$remote_db_pass drop $remote_db -f || true");
            }

            $this->info("Create remote DB...");
            $bar->advance();
            if (!$remote_db_pass) {
                $ssh->exec("mysqladmin -h localhost -u $remote_db_user create $remote_db");
            } else {
                $ssh->exec("mysqladmin -h localhost -u $remote_db_user -p$remote_db_pass create $remote_db");
            }

            $this->info('Migrating...');
            $bar->advance();
            if (!$remote_db_pass) {
                $ssh->exec("mysql -h localhost -u $remote_db_user $remote_db < {$dump_remote_dir}sync_dump.sql");
            } else {
                $ssh->exec("mysql -h localhost -u $remote_db_user -p$remote_db_pass $remote_db < {$dump_remote_dir}sync_dump.sql");
            }

            $confirm = $this->choice("Do you wish to remove the backup files or keep it?", ['remove', 'keep'], 0);
            if ($confirm == 'remove') {
                $this->info('Removing back up files.');
                $bar->advance();
                $ssh->exec("rm {$dump_remote_dir}sync_dump.sql -f");
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
