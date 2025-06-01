# Getting started

## Install the library

```console
composer require fokke/pw-json-api
```

## Create your first service

Create a new service file, like `/site/services/HelloWorldService.php` with the following code.

```php
<?php namespace ProcessWire;

use PwJsonApi\{Service, Response};

class HelloWorldService extends Service
{
  public function __construct()
  {
    parent::__construct();

    // Listen to path /hello-world with GET handler
    $this->addEndpoint('/hello-world')->get(function () {
      return new Response([
        'hello' => 'world',
      ]);
    });
  }
}
```

## Create an API instance

The library utilises ProcessWire URL hooks to create listeners to your endpoints. Therefore you must initialise the API instance either in `/site/ready.php` or in `/site/init.php` file.

```php
use PwJsonApi\Api;

require_once './services/HelloWorldService.php';

// API instance
$api = new Api();
$api->setBasePath('/api');

// Services
$api->addService(new HelloWorldService());

// Run API
$api->run();
```

## Test the endpoint

Open the URL `/api/hello-world`. The server should respond with code 200 and the following JSON:

```json
{
  "data": {
    "hello": "world"
  }
}
```
