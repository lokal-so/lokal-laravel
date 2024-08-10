<?php

namespace LokalSo\ServeLokal;

use Illuminate\Support\ServiceProvider;

class ServeLokalServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ServeLokalCommand::class,
            ]);
        }
    }
}