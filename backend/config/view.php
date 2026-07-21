<?php

$frontendViews = dirname(base_path()).DIRECTORY_SEPARATOR.'frontend'
    .DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views';

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Views live in frontend/resources/views during local development. Docker
    | copies that tree into backend/resources at build time, so we fall back to
    | the default Laravel path when the sibling frontend folder is absent.
    |
    */

    'paths' => [
        is_dir($frontendViews) ? $frontendViews : resource_path('views'),
    ],

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

];
