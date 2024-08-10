# Lokal.so Laravel Provider

this laravel provider is used for adding your artisan command additional argument `php artisan serve-lokal`

| CLI Preview | Browser Preview |
|---|---|
| ![CLI Preview](screenshot1.png)  | ![Browser Preview](screenshot2.png) |

## Installation

1. Lokal Application (free) are required in order to use this, [Download here](https://lokal.so/download)
2. Add composer package to your Laravel project.

```sh
composer require lokal-so/lokal-laravel
```

## Usage

```sh
php artisan serve-lokal \
  --lan-address my-laravel.local \
  --tunnel-name laravel
```