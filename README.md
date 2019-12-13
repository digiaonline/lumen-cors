# Lumen CORS

[![Build Status](https://travis-ci.org/digiaonline/lumen-cors.svg?branch=master)](https://travis-ci.org/digiaonline/lumen-cors)
[![Coverage Status](https://coveralls.io/repos/github/nordsoftware/lumen-cors/badge.svg?branch=master)](https://coveralls.io/github/nordsoftware/lumen-cors?branch=master)
[![Code Climate](https://codeclimate.com/github/nordsoftware/lumen-cors/badges/gpa.svg)](https://codeclimate.com/github/nordsoftware/lumen-cors)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/digiaonline/lumen-cors/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/digiaonline/lumen-cors/?branch=master)
[![StyleCI](https://styleci.io/repos/35571263/shield?style=flat)](https://styleci.io/repos/35571263)
[![Latest Stable Version](https://poser.pugx.org/nordsoftware/lumen-cors/version)](https://packagist.org/packages/nordsoftware/lumen-cors)
[![Total Downloads](https://poser.pugx.org/nordsoftware/lumen-cors/downloads)](https://packagist.org/packages/nordsoftware/lumen-cors)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

[Cross-Origin Resource Sharing](http://enable-cors.org/) (CORS) module for the [Lumen PHP framework](http://lumen.laravel.com/).

## Requirements

- PHP 7.1 or newer
- [Composer](http://getcomposer.org)
- [Lumen](https://lumen.laravel.com/) 5.4 or newer

## Usage

### Installation

Run the following command to install the package through Composer:

```sh
composer require nordsoftware/lumen-cors
```

### Configure

Copy the configuration template in `config/cors.php` to your application's `config` directory and modify according to your needs.
For more information see the [Configuration Files](http://lumen.laravel.com/docs/configuration#configuration-files) section in the Lumen documentation.

Available configuration options:

- **allow_origins** `array` *Origins that are allowed to perform requests, defaults to an empty array. Patterns also accepted, for example \*.foo.com*
- **allow_methods** `array` *HTTP methods that are allowed, defaults to an empty array*
- **allow_headers** `array` *HTTP headers that are allowed, defaults to an empty array*
- **allow_credentials** `boolean` *Whether or not the response can be exposed when credentials are present, defaults to false*
- **expose_headers** `array` *HTTP headers that are allowed to be exposed to the web browser, defaults to an empty array*
- **max_age** `integer` *Indicates how long preflight request can be cached, defaults to 0*

### Bootstrapping

Add the following lines to ```bootstrap/app.php```:

```php
$app->register('Nord\Lumen\Cors\CorsServiceProvider');
```

```php
$app->middleware([
	.....
	'Nord\Lumen\Cors\CorsMiddleware',
]);
```

The module now automatically handles all CORS requests.

## Customizing behavior

While the service can be configured somewhat using `config/cors.php`, some more exotic things such as regular 
expressions for allowed origins cannot. If you need to, you can provide this custom functionality yourself:

1. Extend `CorsService` and override e.g. `isOriginAllowed()`
2. Extend `CorsServiceProvider` and override `registerBindings()`, then register your own service class instead

## Contributing

Please read the [guidelines](.github/CONTRIBUTING.md).

## Running tests

Clone the project and install its dependencies by running:

```sh
composer install
```

Run the following command to run the test suite:

```sh
composer test
```

## License

See [LICENSE](LICENSE).
