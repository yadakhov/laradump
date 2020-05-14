<?php

namespace Yadakhov\Laradump\Commands;

use Illuminate\Console\Command;
use Yadakhov\Laradump\Utility;

class Restore extends Command
{
    protected $signature = 'laradump:restore 
                            {--table= : Table name}
                            {--yes= : accept confirm question}';

    protected $description = 'Perform a restore on every tables.';

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
        $this->database = config('laradump.database', 'mysql');
        $this->tableFolder = config('laradump.table_folder', storage_path('laradump/tables'));
        $this->dataFolder = config('laradump.data_folder', storage_path('laradump/data'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('Starting mysql restore...');

        $configs = config('database.connections.' . $this->database);
        $username = Utility::get($configs, 'username');
        $password = Utility::get($configs, 'password');
        $database = Utility::get($configs, 'database');
        $host = Utility::get($configs, 'host');

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
        // if the user pass in the table just use it.
        $table = $this->option('table');
        $yes = $this->option('yes');

        if (!empty($yes)) {
            if (!$this->confirm('If your tables have new data it will be overwritten!  Do you want to continue?')) {
                // no
                $this->comment('Exiting...');

                return;
            }
        }

        if (strlen($table) > 0) {
            return [$folder . '/' . $table . '.sql'];
        }

        // Scan the directory for files.
        $files = scandir($folder);

        $out = [];
        foreach ($files as $file) {
            if (Utility::endsWith($file, '.sql')) {
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
