<?php

namespace TahirRasheed\MediaLibrary;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver;
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
        if (! app()->configurationIsCached()) {
            $this->mergeConfigFrom(
                __DIR__.'/../config/medialibrary.php', 'medialibrary'
            );
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'medialibrary');

        if (app()->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/medialibrary.php' => config_path('medialibrary.php'),
            ], 'medialibrary-config');

            $this->publishesMigrations([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'medialibrary-migration');
        }

        if (config('medialibrary.image_driver') === 'imagick') {
            config(['image.driver' => Driver::class]);
        } else {
            config(['image.driver' => GdDriver::class]);
        }

        $this->loadViewComponentsAs('medialibrary', [
            FileUpload::class,
            Dropzone::class,
        ]);

        Media::observe(MediaObserver::class);

        Blade::directive('mediaLibraryScript', function () {
            return "<script src='".route('medialibrary.uploader')."?ver=4.0.0'></script>";
        });
    }
}