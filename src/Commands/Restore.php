<?php

namespace Yadakhov\Laradump\Commands;

use Config;
use Illuminate\Console\Command;

class Restore extends Command
{
    protected $signature = 'laradump:restore';

    protected $description = 'Perform a restore.';

    /**
     * @var string folder to store table.
     */
    protected $tableFolder;

    /**
     * @var string folder to store the data.
     */
    protected $dataFolder;

    /**
     * MySqlDump constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->database = Config::get('laradump.database', 'mysql');
        $this->tableFolder = Config::get('laradump.table_folder', storage_path('laradump/tables'));
        $this->dataFolder = Config::get('laradump.data_folder', storage_path('laradump/data'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('Starting mysql restore...');

        if (!$this->confirm('If your tables have new data it will be overwritten!  Do you want to continue?')) {
            // no
            $this->comment('Exiting...');

            return;
        }

        $configs = config('database.connections.' . $this->database);
        $username = array_get($configs, 'username');
        $password = array_get($configs, 'password');
        $database = array_get($configs, 'database');
        $host = array_get($configs, 'host');

        $this->info('Loading table schemas...');
        $tableFiles = $this->getFiles($this->tableFolder);
        foreach ($tableFiles as $file) {
            // Load the table
            $command = sprintf('mysql -u %s -p%s -h %s %s < %s', $username, $password, $host, $database, $file);
            $this->info($this->removePasswordFromCommand($command));
            exec($command);
        }

        $this->info('Loading data for each table...');
        $dataFiles = $this->getFiles($this->dataFolder);
        foreach ($dataFiles as $file) {
            // Load the table
            $command = sprintf('mysql -u %s -p%s -h %s %s < %s', $username, $password, $host, $database, $file);
            $this->info($this->removePasswordFromCommand($command));
            exec($command);
        }
    }

    /**
     * Get all table schema files.
     *
     * @return array
     */
    protected function getFiles($folder)
    {
        // Scan the directory for files.
        $files = scandir($folder);

        $out = [];
        foreach ($files as $file) {
            if (ends_with($file, '.sql')) {
                $out[] = $folder . '/' . $file;
            }
        }

        return $out;
    }

    /**
     * Remove the -ppassword with -p***
     *
     * @param $command
     *
     * @return mixed
     */
    protected function removePasswordFromCommand($command)
    {
        return preg_replace('/-p.* /', '-p**** ', $command) ;
    }
}
