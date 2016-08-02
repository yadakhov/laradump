<?php

namespace Yadakhov\Laradump\Commands;

use Config;
use Illuminate\Console\Command;

class Restore extends Command
{
    protected $signature = 'laradump:restore';

    protected $description = 'Perform a restore.';

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
        parent::__construct();
        $this->database = Config::get('laradump.database', 'mysql');
        $this->folder = Config::get('laradump.folder', storage_path('dumps'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('Starting mysql restore...');

        if (!$this->confirm('If you tables have new data it will be overwritten!  Do you wish to continue?  [yes|no]')) {
            // no
            $this->comment('Exiting...');

            return;
        }

        $configs = config('database.connections.' . $this->database);
        $username = array_get($configs, 'username');
        $password = array_get($configs, 'password');
        $database = array_get($configs, 'database');

        $files = $this->getDumpFiles();

        foreach ($files as $file) {
            $command = sprintf('mysql -u %s -p%s %s < %s', $username, $password, $database, $file);

            $this->info($command);

            exec($command);
        }
    }

    /**
     * Get all dump files files.
     *
     * @return array
     */
    protected function getDumpFiles()
    {
        // Scan the directory for files.
        $files = scandir($this->folder);

        $out = [];
        foreach ($files as $file) {
            if (ends_with($file, '.sql')) {
                $out[] = $this->folder . '/' . $file;
            }
        }

        return $out;
    }
}
