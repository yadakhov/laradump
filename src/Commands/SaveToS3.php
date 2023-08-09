<?php

namespace Yadakhov\Laradump\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SaveToS3 extends Command
{
    protected $signature = 'laradump:save-to-s3';

    protected $description = 'Save laradump folder to s3';

    /**
     * @var string folder to store table.
     */
    protected $tableFolder;

    /**
     * @var string folder to store the data.
     */
    protected $dataFolder;

    /**
     * SaveToS3 constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->tableFolder = config('laradump.table_folder', storage_path('laradump/tables'));
        $this->dataFolder = config('laradump.data_folder', storage_path('laradump/data'));
        $this->s3Prefix = config('laradump.s3_prefix', 'laradump');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('Saving laradump folder to s3...');

        $filesTableFolder = glob($this->tableFolder . '/*');
        $filesDateFolder = glob($this->dataFolder . '/*');
        $files = array_merge($filesTableFolder, $filesDateFolder);

        foreach ($files as $file) {
            $relativePath = basename(dirname($file)) . '/' . basename($file);
            $s3Path = $this->s3Prefix . '/' . $relativePath;

            $this->info("Saving file {$relativePath} to {$s3Path}");
            $fileStream = fopen($file, 'r');
            Storage::disk('s3')->put($s3Path, $fileStream);
        }
    }
}
