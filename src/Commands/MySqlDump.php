<?php

namespace Yadakhov\Laradump\Commands;

use DB;
use File;
use Illuminate\Console\Command;

class MySqlDump extends Command
{
    protected $signature = 'laradump:mysqldump';

    protected $description = 'Perform a MySQL dump.';

    public function handle()
    {
        $this->comment('Starting mysql dump...');

        $configs = config('database.connections.mysql');
        $username = array_get($configs, 'username');
        $password = array_get($configs, 'password');
        $database = array_get($configs, 'database');

        $tables = $this->getAllTables();

        File::makeDirectory(storage_path('dumps/'));

        foreach ($tables as $table) {
            $file = storage_path('dumps/' . $table . '.sql');

            $command = sprintf('mysqldump -u %s -p%s %s %s --skip-dump-date > %s', $username, $password, $database, $table, storage_path('dumps/' . $table . '.sql'));

            $this->info($command);

            exec($command);

            $this->removeServerInformation($file);
        }
    }

    /**
     * Get all the tables in the database.
     *
     * @return array
     */
    public static function getAllTables()
    {
        $connection = config('database.default');
        $databaseName = config('database.connections.' . $connection . '.database');

        $sql = 'SELECT * FROM information_schema.tables WHERE table_schema = ? ORDER BY TABLE_NAME';
        $rows = DB::select($sql, [$databaseName]);
        $out = [];

        foreach ($rows as $row) {
            $out[] = $row->TABLE_NAME;
        }

        return $out;
    }

    public function removeServerInformation($file)
    {
        $lines = file_get_contents($file);
        $lines = explode("\n", $lines);

        foreach ($lines as $key => $line) {
            if (strpos($line, '-- MySQL dump') === 0) {
                unset($lines[$key]);
            }
            if (strpos($line, '-- Server version') === 0) {
                unset($lines[$key]);
                break;
            }
        }

        $lines = implode("\n", $lines);

        file_put_contents($file, $lines);
    }
}
