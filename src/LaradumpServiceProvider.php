<?php

namespace Yadakhov\Laradump;

use Illuminate\Support\ServiceProvider;
use Yadakhov\Laradump\Commands\MySqlDump;
use Yadakhov\Laradump\Commands\Restore;

class LaradumpServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
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
}
