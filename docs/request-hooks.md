---
description: 'Add on-request, before, and after hooks at the API, service, or endpoint level to set headers, validate requests, and modify responses.'
---

# Request hooks

Request hooks can be used to modify the behavior of endpoints. The most common use cases are to validate or modify requests before they are handled, or to modify response data after the request has been handled. For these purposes, [hook arguments](#hook-arguments) will be passed to the hook handler functions.

The examples below use the `hookBefore()` and `hookAfter()` methods, which apply to any request method. There are also [request type-specific hooks](#hook-methods-reference) available.

## Hook scopes

### API hooks

Defined for the whole API instance. These hooks will apply to all endpoints.

::: tip
For authentication and authorization, consider using the dedicated [`authenticate()`](/authentication-overview) and [`authorize()`](/authentication-overview#authorization) methods instead of hooks.
:::

```php
// Require JSON content type for POST requests
$api->hookBeforePost(function ($args) {
  $contentType = $args->event->request->getHeader('Content-Type');

  if (str_contains($contentType, 'application/json') === false) {
    throw (new ApiException('Content-Type must be application/json'))->code(
      415,
    );
  }
});

// Modify response data of every successful response
$api->hookAfter(function ($args) {
  // Inject key to response data
  $args->response->data['_foo'] = 'foo';

  // Include additional top-level keys in the response
  $args->response->with([
    'bar' => 'bar',
  ]);
});
```

### Service hooks

Defined for a single service branch. These hooks will apply to all endpoints within the given service (including child services). Service hooks can be defined directly in the service `init()` method or injected into the service object.

#### Define in init()

```php
$this->hookAfter(function ($args) {
  $args->response->data['_foo'] = 'foo';
});
```

#### Inject in addService() callback

```php
$api->addService(new HelloWorldService(), function ($service) {
  $service->hookAfter(function ($args) {
    $args->response->data['_foo'] = 'foo';
  });
});
```

#### Find installed service and inject

```php
$api->findService('HelloWorldService')?->hookAfter(function ($args) {
  $args->response->data['_foo'] = 'foo';
});
```

### Endpoint hooks

Defined for a single endpoint. Endpoint hooks can be defined directly when creating an endpoint, or they can be injected into the endpoint object.

#### Define directly in endpoint

```php
$this->addEndpoint('/hello-world')
  ->get(function () {
    return new Response([
      'hello' => 'world',
    ]);
  })
  ->hookAfter(function ($args) {
    $args->response->data['_foo'] = 'foo';
  });
```

#### Find existing endpoint and inject

```php
$api->findEndpoint('/api/hello-world')?->hookAfter(function ($args) {
  $args->response->data['_foo'] = 'foo';
});
```

## Multiple hooks

Multiple hooks can affect the single target. [See hook execution order](#hook-execution-order).

```php
$api->hookAfter(function ($args) {
  $args->response->data['_foo'] = 'foo';
});

$api->findService('HelloWorldService')?->hookAfter(function ($args) {
  $args->response->data['_bar'] = 'bar';
});

$api->findEndpoint('/api/hello-world')?->hookAfter(function ($args) {
  $args->response->data['_baz'] = 'baz';
});

$api->findEndpoint('/api/hello-world')?->hookAfter(function ($args) {
  $args->response->data['_qux'] = 'qux';
});
```

## hookOnRequest() <Badge type="tip" text="^2.5" />

`hookOnRequest()` runs as soon as the request is matched to an endpoint — before the `OPTIONS` response, the request method check (`405`), [authentication](/authentication-overview), authorization, and other hooks. Use it for anything that must apply to every response of the API, such as CORS headers.

Headers added to `$args->headers` are added to every response: successful responses, error responses, and `OPTIONS` responses. If the response already has a header with the same name — set by the endpoint handler, an after hook, or an error hook — the response header wins.

```php
// Allow requests from the frontend, including CORS preflight requests
$api->hookOnRequest(function ($args) {
  $args->headers['Access-Control-Allow-Origin'] = 'https://example.com';
  $args->headers['Access-Control-Allow-Credentials'] = 'true';

  if ($args->request->method === 'OPTIONS') {
    // Same methods as in the Allow header of the endpoint
    $args->headers['Access-Control-Allow-Methods'] = implode(', ', [
      'OPTIONS',
      ...$args->endpoint->getAllowedMethods(),
    ]);
    $args->headers['Access-Control-Allow-Headers'] = 'Content-Type';
  }
});
```

The library adds the `Allow` header to `OPTIONS` responses automatically, but browsers only use the `Access-Control-Allow-*` headers for CORS. These headers are a security policy, so the library never adds them on its own.

Like other hooks, on-request hooks can be defined on the API, service, or endpoint level. They run in order API → services → endpoint, and they all share the same `$args->headers`.

To reject a request, throw an `ApiException`. [Error hooks](/error-hooks) are executed, and the headers set so far are included in the error response.

::: warning Limitations

- Requests to paths that do not match any endpoint are not handled by the API, so on-request hooks do not run for them.
- If the request body is malformed JSON, the request is rejected before on-request hooks run, and the error response does not include their headers.

:::

## Hook arguments

You can access the following properties via the `$args` parameter of the handler function. The following properties are always included:

| Property   | Type                     | Description                  |
| ---------- | ------------------------ | ---------------------------- |
| `request`  | `Request`                | [Request object](/requests)  |
| `user`     | `\ProcessWire\User`      | The current ProcessWire user |
| `event`    | `\ProcessWire\HookEvent` | ProcessWire URL hook event   |
| `endpoint` | `Endpoint`               | Requested endpoint           |
| `service`  | `Service`                | Requested service            |
| `services` | `ServiceList`            | List of all parent services  |
| `api`      | `Api`                    | API instance                 |

### hookOnRequest arguments

| Property  | Type    | Description                    |
| --------- | ------- | ------------------------------ |
| `headers` | `array` | Headers to add to the response |

### hookBefore\* arguments

| Property  | Type       | Description              |
| --------- | ---------- | ------------------------ |
| `handler` | `callable` | Endpoint request handler |

### hookAfter\* arguments

| Property   | Type     | Description                            |
| ---------- | -------- | -------------------------------------- |
| `response` | Response | Response from endpoint request handler |

## Hook methods reference

### On request

| Method            | Description                                  |
| ----------------- | -------------------------------------------- |
| `hookOnRequest()` | Hook on request, before any other processing |

### Before request

| Method               | Description                |
| -------------------- | -------------------------- |
| `hookBefore()`       | Hook before any request    |
| `hookBeforeGet()`    | Hook before GET request    |
| `hookBeforePost()`   | Hook before POST request   |
| `hookBeforeHead()`   | Hook before HEAD request   |
| `hookBeforePut()`    | Hook before PUT request    |
| `hookBeforePatch()`  | Hook before PATCH request  |
| `hookBeforeDelete()` | Hook before DELETE request |

### After request

| Method              | Description               |
| ------------------- | ------------------------- |
| `hookAfter()`       | Hook after any request    |
| `hookAfterGet()`    | Hook after GET request    |
| `hookAfterPost()`   | Hook after POST request   |
| `hookAfterHead()`   | Hook after HEAD request   |
| `hookAfterPut()`    | Hook after PUT request    |
| `hookAfterPatch()`  | Hook after PATCH request  |
| `hookAfterDelete()` | Hook after DELETE request |

## Hook execution order

1. API on-request hooks
2. Service on-request hooks
3. Endpoint on-request hooks
4. API before hooks
5. Service before hooks
6. Endpoint before hooks
7. **Request handler**
8. Endpoint after hooks
9. Service after hooks
10. API after hooks

Authentication and authorization run between the on-request hooks and the before hooks. See [Application lifecycle](/lifecycle#request-handling) for the full sequence.
