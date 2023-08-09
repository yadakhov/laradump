<?php

namespace Yadakhov\Laradump;

use Illuminate\Support\ServiceProvider;
use Yadakhov\Laradump\Commands\DropTables;
use Yadakhov\Laradump\Commands\ListTables;
use Yadakhov\Laradump\Commands\MySqlDump;
use Yadakhov\Laradump\Commands\Restore;
use Yadakhov\Laradump\Commands\SaveToS3;
use Yadakhov\Laradump\Commands\SyncFromS3;

class LaradumpServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/config/laradump.php';
        if (function_exists('config_path')) {
            $publishPath = config_path('laradump.php');
        } else {
            $publishPath = base_path('config/laradump.php');
        }
        $this->publishes([$configPath => $publishPath], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerLaradumpMySqlDump();
        $this->registerLaradumpRestore();
        $this->registerListTables();
        $this->registerDropTables();
        $this->registerSaveToS3();
        $this->registerSyncFromS3();
    }

    /**
     * Register the laradump:mysqldump command.
     */
    private function registerLaradumpMySqlDump()
    {
        $this->app->singleton('commands.laradump.mysqldump', function ($app) {
            return $app[MySqlDump::class];
        });
        $this->commands('commands.laradump.mysqldump');
    }

    /**
     * Register the laradump:restore command.
     */
    private function registerLaradumpRestore()
    {
        $this->app->singleton('commands.laradump.restore', function ($app) {
            return $app[Restore::class];
        });
        $this->commands('commands.laradump.restore');
    }

    /**
     * Register the laradump:list-tables command.
     */
    private function registerListTables()
    {
        $this->app->singleton('commands.laradump.list-tables', function ($app) {
            return $app[ListTables::class];
        });
        $this->commands('commands.laradump.list-tables');
    }

    /**
     * Register the laradump:drop-tables command.
     */
    private function registerDropTables()
    {
        $this->app->singleton('commands.laradump.drop-tables', function ($app) {
            return $app[DropTables::class];
        });
        $this->commands('commands.laradump.drop-tables');
    }

    /**
     * Register the laradump:save-to-s3 command.
     */
    private function registerSaveToS3()
    {
        $this->app->singleton('commands.laradump:save-to-s3', function ($app) {
            return $app[SaveToS3::class];
        });
        $this->commands('commands.laradump:save-to-s3');
    }

    /**
     * Register the laradump:sync-from-s3 command.
     */
    private function registerSyncFromS3()
    {
        $this->app->singleton('commands.laradump:sync-from-s3', function ($app) {
            return $app[SyncFromS3::class];
        });
        $this->commands('commands.laradump:sync-from-s3');
    }
}
