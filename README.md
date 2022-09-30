# Media Library for Laravel

This package can associate images with Eloquent models.

## Getting Started

### 1. Install

Run the following command:

```bash
composer require tahirrasheed208/laravel-medialibrary
```

### 2. Publish

Publish config file.

```bash
php artisan vendor:publish --provider="TahirRasheed\MediaLibrary\MediaLibraryServiceProvider" --tag=config
```

### 3. Database

Create table in database.

```bash
php artisan migrate
```

### 4. Configure

You can change the options of your app from `config/medialibrary.php` file

## Testing

```bash
./vendor/bin/phpunit
```

## Changelog

Please see [Releases](../../releases) for more information what has changed recently.

## Contributing

Pull requests are more than welcome. You must follow the PSR coding standards.

## Security

If you discover any security related issues, please email tahirrasheedhtr@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more information.
