<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed request origins
    |--------------------------------------------------------------------------
    |
    | Indicates which origins are allowed to perform requests.
    | Patterns also accepted, for example *.foo.com
    |
    */

    'allow_origins' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed HTTP headers
    |--------------------------------------------------------------------------
    |
    | Indicates which HTTP headers are allowed.
    |
    */

    'allow_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed HTTP methods
    |--------------------------------------------------------------------------
    |
    | Indicates which HTTP methods are allowed.
    |
    */

    'allow_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Whether or not the response can be exposed when credentials are present
    |--------------------------------------------------------------------------
    |
    | Indicates whether or not the response to the request can be exposed when the credentials flag is true.
    | When used as part of a response to a preflight request, this indicates whether or not the actual request
    | can be made using credentials.  Note that simple GET requests are not preflighted, and so if a request
    | is made for a resource with credentials, if this header is not returned with the resource, the response
    | is ignored by the browser and not returned to web content.
    |
    */

    'allow_credentials' => false,

    /*
    |--------------------------------------------------------------------------
    | Exposed headers
    |--------------------------------------------------------------------------
    |
    | Headers that are allowed to be exposed to the web server.
    |
    */

    'expose_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Max age
    |--------------------------------------------------------------------------
    |
    | Indicates how long the results of a preflight request can be cached.
    |
    */

    'max_age' => 0,

];
