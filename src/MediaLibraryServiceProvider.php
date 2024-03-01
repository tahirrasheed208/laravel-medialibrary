<?php

namespace TahirRasheed\MediaLibrary;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use TahirRasheed\MediaLibrary\Models\Media;
use TahirRasheed\MediaLibrary\Observers\MediaObserver;
use TahirRasheed\MediaLibrary\View\Components\Dropzone;
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
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'medialibrary');

        $this->publishes([
            __DIR__.'/../config/medialibrary.php' => config_path('medialibrary.php'),
        ], 'config');

        $this->loadViewComponentsAs('medialibrary', [
            FileUpload::class,
            Dropzone::class,
        ]);

        Media::observe(MediaObserver::class);

        Blade::directive('mediaLibraryScript', function () {
            return "<script src='".route('medialibrary.uploader')."?ver=2.4.6'></script>";
        });
    }
}