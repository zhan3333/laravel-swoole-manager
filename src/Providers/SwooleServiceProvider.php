<?php

namespace Zhan3333\LaravelSwooleManager\Providers;

use Illuminate\Support\ServiceProvider;
use Zhan3333\LaravelSwooleManager\Commands\SwooleCommand;
use Zhan3333\LaravelSwooleManager\Commands\SwooleListCommand;
use Zhan3333\LaravelSwooleManager\SwooleManager;

class SwooleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SwooleCommand::class,
                SwooleListCommand::class,
            ]);
        }
//        $this->publishes([
//            __DIR__ . '/../../config/swoole.php' => config_path('swoole.php'),
//        ]);
    }

    public function register()
    {
//        $this->mergeConfigFrom(
//            __DIR__ . '/../../config/swoole.php', 'swoole'
//        );
        $this->app->singleton(SwooleManager::class, function () {
            return new SwooleManager();
        });
    }
}
