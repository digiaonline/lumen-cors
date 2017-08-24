# Lumen CORS

[![Build Status](https://travis-ci.org/digiaonline/lumen-cors.svg?branch=master)](https://travis-ci.org/digiaonline/lumen-cors)
[![Coverage Status](https://coveralls.io/repos/github/nordsoftware/lumen-cors/badge.svg?branch=master)](https://coveralls.io/github/nordsoftware/lumen-cors?branch=master)
[![Code Climate](https://codeclimate.com/github/nordsoftware/lumen-cors/badges/gpa.svg)](https://codeclimate.com/github/nordsoftware/lumen-cors)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nordsoftware/lumen-cors/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nordsoftware/lumen-cors/?branch=master)
[![StyleCI](https://styleci.io/repos/35571263/shield?style=flat)](https://styleci.io/repos/35571263)
[![Latest Stable Version](https://poser.pugx.org/nordsoftware/lumen-cors/version)](https://packagist.org/packages/nordsoftware/lumen-cors)
[![Total Downloads](https://poser.pugx.org/nordsoftware/lumen-cors/downloads)](https://packagist.org/packages/nordsoftware/lumen-cors)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Gitter](https://img.shields.io/gitter/room/norsoftware/open-source.svg?maxAge=2592000)](https://gitter.im/nordsoftware/open-source)

[Cross-Origin Resource Sharing](http://enable-cors.org/) (CORS) module for the [Lumen PHP framework](http://lumen.laravel.com/).

**NOTE:** Branch 5.3 uses Lumen framework 5.3. Only bug-fixes 1.7.X should be tagged in the 5.3 branch.

## Requirements

- PHP 5.6 or newer
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

- **allow_origins** `array` *Origins that are allowed to perform requests, defaults to an empty array*
- **allow_methods** `array` *HTTP methods that are allowed, defaults to an empty array*
- **allow_headers** `array` *HTTP headers that are allowed, defaults to an empty array*
- **allow_credentials** `boolean` *Whether or not the response can be exposed when credentials are present, defaults to false*
- **expose_headers** `array` *HTTP headers that are allowed to be exposed to the web browser, defaults to an empty array*
- **max_age** `integer` *Indicates how long preflight request can be cached, defaults to 0*
- **origin_not_allowed** `callable` *Creates the response if the origin is not allowed*
- **method_not_allowed** `callable` *Creates the response if the method is not allowed*
- **header_not_allowed** `callable` *Creates the response if the header is not allowed*

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

## Contributing

Please read the [guidelines](.github/CONTRIBUTING.md).

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
