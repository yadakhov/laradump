# Laradump is a mysqldump wrapper for laravel.

### Install from packagist

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

### Create the config/laradump.php file.

```
php artisan vendor:publish
```
