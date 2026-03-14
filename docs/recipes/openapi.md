---
description: 'Generate OpenAPI specifications from your services using swagger-php attributes and named endpoint methods.'
---

# OpenAPI documentation

You can use [swagger-php](https://zircote.github.io/swagger-php/) attributes to generate an [OpenAPI](https://www.openapis.org/) spec directly from your services. The key pattern is to define endpoint handlers as named class methods instead of inline closures, giving the attributes a target to attach to.

## Install swagger-php

```console
composer require --dev zircote/swagger-php
```

## Extend the Api class

API-level metadata like the title and version belongs on an `Api` subclass. Use the `#[OA\Info]` attribute to define it:

```php
<?php namespace ProcessWire;

use PwJsonApi\Api;
use OpenApi\Attributes as OA;

#[
  OA\Info(
    title: 'Documented API',
    version: '1.0.0',
    description: 'Example API with OpenAPI documentation',
  ),
]
class DocumentedApi extends Api {}
```

## Add attributes to a service

Define each handler as a named method and annotate it with the matching OpenAPI attribute. In `init()`, pass the handler using first-class callable syntax (`$this->greet(...)`):

::: info
The `path` values in the attributes must include the full route as seen by the client (base path + endpoint path).
:::

```php
<?php namespace ProcessWire;

use PwJsonApi\{Service, Response, EndpointHandlerArgs};
use OpenApi\Attributes as OA;

class DocumentedService extends Service
{
  protected function init()
  {
    $this->addEndpoint('/greet')
      ->get($this->greet(...))
      ->post($this->createGreeting(...));
  }

  #[
    OA\Get(
      path: '/documented-api/greet',
      operationId: 'greet',
      parameters: [
        new OA\Parameter(
          name: 'name',
          in: 'query',
          required: false,
          schema: new OA\Schema(type: 'string'),
          description: 'Greeting message',
        ),
      ],
      responses: [
        new OA\Response(response: 200, description: 'Greeting response'),
      ],
    ),
  ]
  protected function greet(EndpointHandlerArgs $args): Response
  {
    return new Response([
      'greeting' => $args->request->queryParam('name') ?? null,
    ]);
  }

  #[
    OA\Post(
      path: '/documented-api/greet',
      operationId: 'createGreeting',
      requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
          properties: [
            new OA\Property(
              property: 'name',
              type: 'string',
              description: 'Greeting message',
            ),
          ],
        ),
      ),
      responses: [
        new OA\Response(response: 201, description: 'Greeting created'),
      ],
    ),
  ]
  protected function createGreeting(EndpointHandlerArgs $args): Response
  {
    return (new Response([
      'greeting' => $args->request->body['name'] ?? null,
    ]))->code(201);
  }
}
```

## Register the API

Wire everything up in `ready.php` the same way you would with a regular API:

```php
(new DocumentedApi())
  ->setBasePath('documented-api')
  ->addService(new DocumentedService())
  ->run();
```

## Generate the spec

Use the `openapi` CLI tool to scan your service files and output the spec. Assuming the API classes are placed in `site/lib`:

```console
./vendor/bin/openapi site/lib -o openapi.yaml
```

To generate JSON instead:

```console
./vendor/bin/openapi site/lib --format json -o openapi.json
```

## View and explore the spec

The generated spec file can be opened in an API client or documentation viewer:

- [Bruno](https://www.usebruno.com/) — open-source API client that can import OpenAPI specs for testing and exploration
- [Swagger UI](https://swagger.io/tools/swagger-ui/) — interactive documentation viewer that renders the spec as a browsable reference with a built-in request console
