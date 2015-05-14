# Lumen CORS

[![Build Status](https://travis-ci.org/nordsoftware/lumen-cors.svg?branch=master)](https://travis-ci.org/nordsoftware/lumen-cors)
[![Latest Stable Version](https://poser.pugx.org/nordsoftware/lumen-cors/version)](https://packagist.org/packages/nordsoftware/lumen-cors) [![Latest Unstable Version](https://poser.pugx.org/nordsoftware/lumen-cors/v/unstable)](//packagist.org/packages/nordsoftware/lumen-cors) [![Total Downloads](https://poser.pugx.org/nordsoftware/lumen-cors/downloads)](https://packagist.org/packages/nordsoftware/lumen-cors)

[CORS](http://enable-cors.org/) module for the [Lumen PHP framework](http://lumen.laravel.com/).

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

Copy the configuration template in `config/cors.php` to your application's `config` directory and modifying according to your needs. For more information see the [Configuration Files](http://lumen.laravel.com/docs/configuration#configuration-files) section in the Lumen documentation.

The available configurations are:

- **allowOrigins** - *Origins that are allowed to perform requests, defaults to an empty array*
- **allowHeaders** - *HTTP headers that are allowed, defaults to an empty array*
- **allowMethods** - *HTTP methods that are allowed, defaults to an empty array*
- **allowCredentials** - *Whether or not the response can be exposed when credentials are present, defaults to false*
- **exposeHeaders** - *HTTP Headers that are allowed to be exposed to the web server, defaults to an empty array*
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
- Create pull requests for the *develop* branch

## License

See [LICENSE](LICENSE).
