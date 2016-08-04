<?php

namespace Yadakhov\Laradump\Commands;

use Config;
use DB;
use File;
use Illuminate\Console\Command;

class MySqlDump extends Command
{
    protected $signature = 'laradump:mysqldump';

    protected $description = 'Perform a MySQL dump.';

    /**
     * @var string default database connection
     */
    protected $database;

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

    public function handle()
    {
        $this->comment('Starting mysql dump...');

        $this->createFolders();

        $configs = config('database.connections.' . $this->database);
        $username = array_get($configs, 'username');
        $password = array_get($configs, 'password');
        $database = array_get($configs, 'database');
        $host = array_get($configs, 'host');

        $tables = $this->getAllTables();

        foreach ($tables as $table) {
            $tableFile = $this->tableFolder . '/' . $table . '.sql';
            $dataFile = $this->dataFolder . '/' . $table . '.sql';

            // Dump the table schema
            $command = sprintf('mysqldump -u %s -p%s %s -h %s %s --no-data --skip-comments > %s', $username, $password, $database, $host, $table, $tableFile);
            $this->info($this->removePasswordFromCommand($command));
            exec($command);

            // Dump the data
            $command = sprintf('mysqldump -u %s -p%s %s -h %s %s --skip-dump-date --extended-insert --skip-comments > %s', $username, $password, $database, $host, $table, $dataFile);
            $this->info($this->removePasswordFromCommand($command));
            exec($command);
        }
    }

    /**
     * Create the dump folder.
     */
    protected function createFolders()
    {
        if (!File::exists($this->tableFolder)) {
            File::makeDirectory($this->tableFolder, null, true);
        }
        if (!File::exists($this->dataFolder)) {
            File::makeDirectory($this->dataFolder, null, true);
        }
    }

    /**
     * Get all the tables in the database.
     *
     * @return array
     */
    protected function getAllTables()
    {
        $configs = config('database.connections.' . $this->database);
        $database = array_get($configs, 'database');

        $sql = 'SELECT * FROM information_schema.tables WHERE table_schema = ? ORDER BY TABLE_NAME';
        $rows = DB::select($sql, [$database]);
        $out = [];

        foreach ($rows as $row) {
            $out[] = $row->TABLE_NAME;
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
