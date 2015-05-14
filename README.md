# Lumen CORS

[![Build Status](https://travis-ci.org/nordsoftware/lumen-cors.svg?branch=master)](https://travis-ci.org/nordsoftware/lumen-cors)
[![Latest Stable Version](https://poser.pugx.org/nordsoftware/lumen-cors/version)](https://packagist.org/packages/nordsoftware/lumen-cors) 
[![Total Downloads](https://poser.pugx.org/nordsoftware/lumen-cors/downloads)](https://packagist.org/packages/nordsoftware/lumen-cors)
[![License](https://poser.pugx.org/nordsoftware/lumen-cors/license)](https://packagist.org/packages/nordsoftware/lumen-cors)

[Cross-Origin Resource Sharing](http://enable-cors.org/) (CORS) module for the [Lumen PHP framework](http://lumen.laravel.com/).

## Requirements

- PHP 5.4 or newer
- [Composer](http://getcomposer.org)

## Usage

### Installation

Run the following command to install the package through Composer:

```sh
composer require nordsoftware/lumen-cors
```

### Configure

Copy the configuration template in `config/cors.php` to your application's `config` directory and modifying according to your needs. 
For more information see the [Configuration Files](http://lumen.laravel.com/docs/configuration#configuration-files) section in the Lumen documentation.

Available configuration options:

- **allowOrigins** - *Origins that are allowed to perform requests, defaults to an empty array*
- **allowHeaders** - *HTTP headers that are allowed, defaults to an empty array*
- **allowMethods** - *HTTP methods that are allowed, defaults to an empty array*
- **allowCredentials** - *Whether or not the response can be exposed when credentials are present, defaults to false*
- **exposeHeaders** - *HTTP Headers that are allowed to be exposed to the web browser, defaults to an empty array*
- **maxAge** - *Indicates how long preflight request can be cached, defaults to 0*

### Bootstrapping

Add the following lines to ```bootstrap/app.php```:

```php
$app->configure('cors');
```

```php
$app->register('Nord\Lumen\Cors\CorsServiceProvider');
```

```php
$app->middleware([
	.....
	'Nord\Lumen\Cors\Middleware\CorsMiddleware',
]);
```

The module now automatically handles all CORS requests. 

## Contributing

Please note the following guidelines before submitting pull requests:

- Use the [PSR-2 coding style](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
- All new features must be covered by unit tests
- Always create pull requests to the *develop* branch

## Running tests

Clone the project and install its dependencies by running:

```sh
composer install
```

Run the following command to run the test suite:

```sh
vendor/bin/codecept run unit
```

## License

See [LICENSE](LICENSE).
