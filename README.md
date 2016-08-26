# Laradump 

A wrapper package to run mysqldump from laravel console commands.

## Installation

### Install from [packagist](https://packagist.org/packages/yadakhov/laradump)

```
composer require yadakhov/laradump
```

Or add to your composer.json

```
    "require": {
        "yadakhov/laradump": "^1.0"
    },
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
  laradump:mysqldump  Perform a MySQL dump.
  laradump:restore    Perform a restore.
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

####Ensure the storage folder is writable.

```
mkdir storage/laradump
chmod o+w -R storage
```
