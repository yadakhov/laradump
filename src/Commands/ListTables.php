<?php

namespace Yadakhov\Laradump\Commands;

use DB;
use Illuminate\Console\Command;

class ListTables extends Command
{
    protected $signature = 'laradump:list';

    protected $description = 'List all tables to perform individually.';

    /**
     * @var string folder to store table.
     */
    protected $tableFolder;

    /**
     * MySqlDump constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->database = config('laradump.database', 'mysql');
        $this->tableFolder = config('laradump.table_folder', storage_path('laradump/tables'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('List of tables in the database:');

        $tables = $this->getTables();

        foreach ($tables as $table) {
            $this->comment('php artisan laradump:mysqldump --table=' . $table);
        }

        $this->info('List of table you can restore:');

        $files = $this->getFiles($this->tableFolder);

        foreach ($files as $file) {
            $this->comment('php artisan laradump:restore --table=' . $file);
        }
    }

    /**
     * Get all the tables in the database.
     */
    protected function getTables()
    {
        $sql = 'SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = ?';

        $configs = config('database.connections.' . $this->database);
        $database = array_get($configs, 'database');

        $rows = DB::select($sql, [$database]);

        $out = [];

        foreach ($rows as $row) {
            $out[] = $row->TABLE_NAME;
        }

        return $out;
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
                // remove the .sql
                $file = substr($file, 0, -4);
                $out[] = $file;
            }
        }

        return $out;
    }
}
