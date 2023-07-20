<?php

return [

    /*
     * The disk on which to store added files.
     */
    'disk_name' => env('MEDIA_DISK', 'public'),

    /*
     * The engine that should perform the image conversions.
     * Should be either `gd` or `imagick`.
     */
    'image_driver' => env('IMAGE_DRIVER', 'gd'),

    /*
     * The maximum file size of an item in bytes.
     */
    'max_file_size' => 1024 * 1024 * 2, // 2MB

    /*
     * The maximum files upload in dropzone.
     */
    'max_files' => 20,

    /*
     * Leave empty to use the default queue.
     */
    'queue_name' => '',

    /*
     * Generate thumbnails for faster loading.
     * We recommend you to enable this.
     */
    'thumbnails' => [
        'generate' => true,
        'width' => 200,
        'height' => 200,
    ],

    /*
     * Define your blade stack name
     * where we push our uploader scripts.
     */
    'stack' => 'footer',

    /*
     * Define collection & disk for laravel-settings package.
     */
    'laravel_settings' => [
        'collection' => 'settings',
        'disk' => env('MEDIA_DISK', 'public'),
    ],
];
