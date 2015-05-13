# lumen-cors

CORS module for the Lumen PHP framework.

## Requirements

- PHP >= 5.5

## Usage

### Install through Composer

Run the following command to install the package:

```sh
composer require nordsoftware/lumen-cors
```

### Register the Service Provider

Add the following lines to ```bootstrap/app.php```:

```php
$app->register('Nord\Lumen\Cors\CorsServiceProvider');
```

```php
$app->middleware([
	.....
	'Nord\Lumen\Cors\Middleware\CorsMiddleware',
]);
```

The middleware will now automatically handle the CORS requests. 

### Configure

Copy ```config/cors.php``` into ```config``` and modify it if necessary.

The available configurations are:

- **allowOrigins** - Indicates which origins are allowed to perform requests, defaults to an empty array
- **allowHeaders** - Indicates which HTTP headers are allowed, defaults to an empty array
- **allowMethods** - Indicates which HTTP methods are allowed, defaults to an empty array
- **allowCredentials** - Whether or not the response can be exposed when credentials are present, defaults to false
- **exposeHeaders** - Headers that are allowed to be exposed to the web server, defaults to an empty array
- **maxAge** - Indicates how long the results of a preflight request can be cached, defaults to 0

## Contributing

Contributions are most welcome. Please note the following guidelines before submitting pull requests:

- Format code according to the [PSR standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)

## License

See [LICENSE](LICENSE).
