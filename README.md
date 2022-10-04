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

Migrate your database.

```bash
php artisan migrate
```

## Usage

Your Eloquent models should use the `TahirRasheed\MediaLibrary\Traits\HasMedia` trait.

Use blade component to add file uploader in your form.

```php
<x-medialibrary-file-upload name="image" />
```

For display old image in edit page.

```php
<x-medialibrary-file-upload name="image" :model="$model" />
```

## Upload

```php
$model = Model::find(1);
$model->handleMedia($request->toArray());
```

If your file input name is not `image` then define second param.

```php
$model->handleMedia($request->toArray(), 'banner');
```

Upload to specific collection.

```php
$model->toMediaCollection('images')->handleMedia($request->toArray());
```

You can define default collection at eloquent level. Add below function in your model.

```php
public function defaultCollection(): string
{
    return 'post_images';
}
```

Upload to specific disk.

```php
$model->useDisk('s3')->handleMedia($request->toArray());
```

## Changelog

Please see [Releases](../../releases) for more information what has changed recently.

## Contributing

Pull requests are more than welcome. You must follow the PSR coding standards.

## Security

If you discover any security related issues, please email tahirrasheedhtr@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more information.
