# Laradump 

A wrapper package to run mysqldump from laravel console commands.

## Installation

### Install from [packagist](https://packagist.org/packages/yadakhov/laradump)

```
composer require yadakhov/laradump
```

### Add to providers array in `config/app.php`

```php
    'providers' => [
        // ...others

        Yadakhov\Laradump\LaradumpServiceProvider::class,
    ],
```

### Create configuration file `config/laradump.php`.

```
php artisan vendor:publish
```

## Laradump commands

Once the LaradumpServiceProvider is registered, the commands will show up when you do a `php artisan`.

```
php artisan
```

```bash
 ...
 laradump
  laradump:drop-tables        Drop tables that do not have backup files.
  laradump:list               List all tables to perform individually.
  laradump:mysqldump          Perform a MySQL dump on every tables.
  laradump:restore            Perform a restore on every tables.
  laradump:save-to-s3         Save laradump folder to s3
  laradump:sync-from-s3       Sync laradump folder from s3
 ...
```

### Doing a mysqldump

```
php artisan laradump:mysqldump
```

Will perform a mysqldump of each table in your database and store it in the `storage/dumps` folder.

### Doing a mysql restore

```
php artisan laradump:restore
```

Will load all sql files in `/storage/dumps.`

### Perform backup and restore on individual table

```
php artisan laradump:mysqldump  --table=user
php artisan laradump:restore  --table=user

# To see a list of possible tables
php artisan laradump:list 
```

### Ensure the storage folder is writable.

```
# Create the tables for storing the files
mkdir storage/laradump/tables
mkdir storage/laradump/data

sudo chmod o+w -R storage
```

### Add ignore the dump files in git

```
# add line to your .gitignore
/storage/laradump
```
