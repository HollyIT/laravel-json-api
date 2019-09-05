<?php

namespace Hollyit\LaravelJsonApi;

use Illuminate\Support\ServiceProvider;

class LaravelJsonApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('jsonapi.php'),
            ], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'jsonapi');
        $this->app->resolving(CollectionRequest::class, function ($request, $app) {
            CollectionRequest::createFrom($app['request'], $request);
        });
        $this->app->singleton(JsonApi::class);
        $this->app->alias(JsonApi::class, 'json-api');
    }
}
