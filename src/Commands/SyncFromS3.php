<?php

namespace Yadakhov\Laradump\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SyncFromS3 extends Command
{
    protected $signature = 'laradump:sync-from-s3';

    protected $description = 'Sync laradump folder from s3';

    /**
     * @var string S3 prefix.
     */
    protected $s3Prefix;

    /**
     * SaveToS3 constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->s3Prefix = config('laradump.s3_prefix', 'laradump');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('Syncing laradump folder from s3...');

        $files = Storage::disk('s3')->files($this->s3Prefix, true);

        foreach ($files as $file) {
            $localPath = $this->getLocalPath($file);
            $this->info("Syncing file {$file} to {$localPath}");
            $localFolderPath = dirname($localPath);
            if (!File::exists($localFolderPath)) {
                File::makeDirectory($localFolderPath, 0777, true, true);
            }

            $s3FileStream = Storage::disk('s3')->readStream($file);
            $localFileStream = fopen($localPath, 'w');

            while (!feof($s3FileStream)) {
                fwrite($localFileStream, fread($s3FileStream, 100000));
            }

            fclose($s3FileStream);
            fclose($localFileStream);
        }
    }

    /**
     * @param $filePath
     * @return string
     */
    protected function getLocalPath($filePath)
    {
        $parts = explode('/', $filePath);
        list($folder, $filename) = array_slice($parts, -2);
        return storage_path("laradump/{$folder}/{$filename}");
    }
}
