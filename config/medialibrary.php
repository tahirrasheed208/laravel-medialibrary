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
];
