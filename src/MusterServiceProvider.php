<?php

namespace Juddos545\Muster;

use Illuminate\Support\ServiceProvider;
use Juddos545\Muster\Commands\GenerateValidationCommand;

class MusterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateValidationCommand::class,
            ]);
        }
    }

    public function register()
    {

    }
}