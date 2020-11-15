<?php

namespace Spatie\Backup;

use Illuminate\Support\ServiceProvider;
use Spatie\Backup\Commands\BackupCommand;
use Spatie\Backup\Commands\CleanupCommand;
use Spatie\Backup\Commands\ListCommand;
use Spatie\Backup\Commands\MonitorCommand;
use Spatie\Backup\Helpers\ConsoleOutput;
use Spatie\Backup\Notifications\EventHandler;
use Spatie\Backup\Tasks\Cleanup\CleanupStrategy;

class BackupServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/backup.php' => config_path('backup.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/lang' => "{$this->app['path.lang']}/vendor/backup",
        ]);

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang/', 'backup');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/backup.php', 'backup');

        $this->app['events']->subscribe(EventHandler::class);

        $this->app->bind('command.backup:run', BackupCommand::class);
        $this->app->bind('command.backup:clean', CleanupCommand::class);
        $this->app->bind('command.backup:list', ListCommand::class);
        $this->app->bind('command.backup:monitor', MonitorCommand::class);

        $this->app->bind(CleanupStrategy::class, config('backup.cleanup.strategy'));

        $this->commands([
            'command.backup:run',
            'command.backup:clean',
            'command.backup:list',
            'command.backup:monitor',
        ]);

        $this->app->singleton(ConsoleOutput::class);
    }
}
