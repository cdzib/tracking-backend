<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Decorador para la documentación OpenAPI
        $this->app->extend(\ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface::class, function ($decorated, $app) {
            return new \App\OpenApi\CustomOpenApiFactory($decorated);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
