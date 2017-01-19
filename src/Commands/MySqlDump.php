<?php

namespace Yadakhov\Laradump\Commands;

use DB;
use File;
use Illuminate\Console\Command;

class MySqlDump extends Command
{
    protected $signature = 'laradump:mysqldump {--table=}';

    protected $description = 'Perform a MySQL dump on every tables.';

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
        $this->database = config('laradump.database', 'mysql');
        $this->tableFolder = config('laradump.table_folder', storage_path('laradump/tables'));
        $this->dataFolder = config('laradump.data_folder', storage_path('laradump/data'));
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

        $table = $this->option('table');

        if (strlen($table) > 0) {
            $tables  = [$table];
        } else {
            $tables = $this->getAllTables();
        }

        foreach ($tables as $table) {
            $tableFile = $this->tableFolder . '/' . $table . '.sql';
            $dataFile = $this->dataFolder . '/' . $table . '.sql';

            if (config('laradump.remove_auto_increment', true)) {
                $dumpCommand = "mysqldump -u %s -p%s %s -h %s %s --no-data --skip-comments | sed 's/ AUTO_INCREMENT=[0-9]*\\b//' > %s";
            } else {
                $dumpCommand = 'mysqldump -u %s -p%s %s -h %s %s --no-data --skip-comments > %s';
            }

            // Dump the table schema
            $command = sprintf($dumpCommand, $username, $password, $database, $host, $table, $tableFile);
            $this->info($this->removePasswordFromCommand($command));
            exec($command);

            // Dump the data
            $command = sprintf('mysqldump -u %s -p%s %s -h %s %s --no-create-info --extended-insert --skip-comments > %s', $username, $password, $database, $host, $table, $dataFile);
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
            File::makeDirectory($this->tableFolder, 0775, true);
        }
        if (!File::exists($this->dataFolder)) {
            File::makeDirectory($this->dataFolder, 0775, true);
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
