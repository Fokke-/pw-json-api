---
description: 'Add before and after hooks at the API, service, or endpoint level to validate requests and modify responses.'
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

### hookBefore\* arguments

| Property  | Type       | Description              |
| --------- | ---------- | ------------------------ |
| `handler` | `callable` | Endpoint request handler |

### hookAfter\* arguments

| Property   | Type     | Description                            |
| ---------- | -------- | -------------------------------------- |
| `response` | Response | Response from endpoint request handler |

## Hook methods reference

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

1. API before hooks
2. Service before hooks
3. Endpoint before hooks
4. **Request handler**
5. Endpoint after hooks
6. Service after hooks
7. API after hooks
