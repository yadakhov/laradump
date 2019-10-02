<?php

namespace Yadakhov\Laradump\Commands;

use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class DropTables extends Command
{
    protected $signature = 'laradump:drop-tables';

    protected $description = 'Drop tables that do not have backup files.';

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
        $files = $this->getFiles($this->tableFolder);

        $diff = array_diff($tables, $files);

        if (empty($diff)) {
            $this->info('There no differences...');
            die;
        }

        foreach ($diff as $table) {
            if ($this->confirm('Do you want to drop ' . $table . '?')) {
                $this->dropTable($table);
            }
        }
    }

    /**
     * Get all the tables in the database.
     */
    protected function getTables()
    {
        $sql = 'SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = ?';

        $configs = config('database.connections.' . $this->database);
        $database = Arr::get($configs, 'database');

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

    /**
     * Drop table.
     */
    protected function dropTable($table)
    {
        $sql = 'DROP TABLE `'.$table.'`';

        return DB::statement($sql);
    }
}
