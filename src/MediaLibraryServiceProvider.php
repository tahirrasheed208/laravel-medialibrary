<?php

namespace TahirRasheed\MediaLibrary;

use Illuminate\Support\ServiceProvider;
use TahirRasheed\MediaLibrary\View\Components\FileUpload;

class MediaLibraryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/medialibrary.php', 'medialibrary'
        );

        $this->app->singleton('media', function () {
            return new MediaHelper();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'medialibrary');

        $this->publishes([
            __DIR__.'/../config/medialibrary.php' => config_path('medialibrary.php'),
        ], 'config');

        $this->loadViewComponentsAs('medialibrary', [
            FileUpload::class,
        ]);
    }
}