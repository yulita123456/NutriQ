<?php

return [

    'default' => env('FILESYSTEM_DISK', 'public'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        // ====================================================================
        // INI PERUBAHAN PALING PENTING
        // ====================================================================
        'public' => [
            'driver' => 'local',
            // Mengubah root ke folder public, bukan storage
            'root' => public_path('uploads'),

            // URL akan menjadi domain.com/uploads/namafile.jpg
            'url' => env('APP_URL').'/uploads',

            'visibility' => 'public',
        ],
        // ====================================================================
        // AKHIR DARI PERUBAHAN
        // ====================================================================


        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

    ],

    // Kita tidak pakai link lagi, jadi bagian ini tidak relevan
    'links' => [
        // public_path('storage') => storage_path('app/public'),
    ],

];
