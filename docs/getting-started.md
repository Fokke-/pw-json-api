# Getting started

Please check that your environment meets the minimum system requirements:

- **PHP** >=8.2
- **ProcessWire** >=3.0.173

## Install the library

```console
composer require fokke/pw-json-api
```

## Create your first service

Create a new service file, such as `/site/services/HelloWorldService.php` with the following code:

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

The library utilizes ProcessWire URL hooks to create listeners for your endpoints. Therefore, you must initialize the API instance either in the `/site/ready.php` or `/site/init.php` file.

```php
use PwJsonApi\Api;

// Consider auto-loading all the services
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

Open the URL `/api/hello-world`. The server should respond with status code 200 and the following JSON:

```json
{
  "data": {
    "hello": "world"
  }
}
```
