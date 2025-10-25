<?php

namespace Fragly\LaravelEnvsync;

use Fragly\LaravelEnvsync\Commands\EnvDiffCommand;
use Fragly\LaravelEnvsync\Commands\EnvSyncCommand;
use Fragly\LaravelEnvsync\Services\EnvSyncService;
use Illuminate\Support\ServiceProvider;

class EnvSyncServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EnvSyncService::class, function () {
            return new EnvSyncService();
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                EnvDiffCommand::class,
                EnvSyncCommand::class,
            ]);
        }
    }
}