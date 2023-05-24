<?php

use Illuminate\Support\Str;

return [
    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | The database connection.
    */
    'database' => 'mysql',

    /*
    |--------------------------------------------------------------------------
    | Table Folder
    |--------------------------------------------------------------------------
    |
    | Where to store the table files.
    */
    'table_folder' => storage_path('laradump/tables'),

    /*
    |--------------------------------------------------------------------------
    | Data Folder
    |--------------------------------------------------------------------------
    |
    | Where to store the data files.
    */
    'data_folder' => storage_path('laradump/data'),

    /*
    |--------------------------------------------------------------------------
    | Remove auto increment
    |--------------------------------------------------------------------------
    | Remove auto increment from CREATE TABLE ... AUTO_INCREMENT=1000
    */
    'remove_auto_increment' => true,

    /*
    |--------------------------------------------------------------------------
    | S3 prefix path
    |--------------------------------------------------------------------------
    | S3 prefix path to store the files without leading or trailing slashes.
    */
    's3_prefix' => 'laradump/' . Str::slug(env('APP_NAME', 'laravel')),
];
