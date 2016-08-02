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
     * @var string folder to stored the files
     */
    protected $folder;

    /**
     * MySqlDump constructor.
     */
    public function __construct()
    {
        $this->database = Config::get('laradump.database', 'mysql');
        $this->folder = Config::get('laradump.folder', storage_path('dumps'));
    }

    public function handle()
    {
        $this->comment('Starting mysql dump...');

        $configs = config('database.connections.' . $this->database);
        $username = array_get($configs, 'username');
        $password = array_get($configs, 'password');
        $database = array_get($configs, 'database');

        $tables = $this->getAllTables();

        File::makeDirectory($this->folder);

        foreach ($tables as $table) {
            $file = $this->folder . '/' . $table . '.sql';

            $command = sprintf('mysqldump -u %s -p%s %s %s --skip-dump-date > %s', $username, $password, $database, $table, $file);

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
    public function getAllTables()
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
     * Remove the server information from the mysql dump file.
     * This is done so git won't see any changes if there is no data change.
     *
     * @param $file
     */
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
