# Request hooks

Request hooks can be used to modify the behaviour of the endpoints. The most common use case is to check for authorisation before the request is handled, or to modify response data after the request has been handled. For this purpose [hook arguments](#hook-arguments) will be passed to hook handler function.

The examples below use `hookBefore()` or `hookAfter()` methods, which will apply to any request method. There are also [request type specific hooks](#hook-methods-reference) available.

## API hooks

Defined for the whole API instance. Hooks will apply to all endpoints.

```php
// Simple auth check for all requests, with any request method
$api->hookBefore(function () {
  if (
    wire()->user->isLoggedin() === false ||
    wire()->user->hasRole('rabbit') === false
  ) {
    throw (new ApiException())->code(401)->with([
      'login_url' => 'https://example.com/login',
    ]);
  }
});

// Modify response data of every successful response
$api->hookAfter(function ($args) {
  // Inject key to response data
  $args->response->data['_foo'] = 'bar';

  // Include additional top level keys to the response
  $args->response->with([
    'baz' => 'qux',
  ]);
});
```

## Service hooks

Defined for a single service branch. Hooks will apply to all endpoints (child services included) within the given service. These can be defined directly in service constructor or they can be injected to service object.

### Define in service constructor

```php
$this->hookAfter(function ($args) {
  $args->response->data['_foo'] = 'bar';
});
```

### Inject in addService() callback

```php
$api->addService(new HelloWorldService(), function ($service) {
  $service->hookAfter(function ($args) {
    $args->response->data['_foo'] = 'bar';
  });
});
```

### Find installed service and inject

```php
$api->findService('HelloWorldService')?->hookAfter(function ($args) {
  $args->response->data['_foo'] = 'bar';
});
```

## Endpoint hooks

Defined for a single endpoint. These can be defined directly when creating an endpoint (which would not make much sense), or they can be injected to the endpoint object.

```php
$api->findEndpoint('/api/hello-world')?->hookAfter(function ($args) {
  $args->response->data['_foo'] = 'bar';
});
```

## Hook arguments

You can access hook arguments in `$args` argument of the hook handler function. The following properties are always included:

| Property          | Type                     | Description                 |
| ----------------- | ------------------------ | --------------------------- |
| `$args->event`    | `\ProcessWire\HookEvent` | ProcessWire URL hook event  |
| `$args->method`   | `string`                 | Request method              |
| `$args->endpoint` | `Endpoint`               | Requested endpoint          |
| `$args->service`  | `Service`                | Requested service           |
| `$args->services` | `ServiceList`            | List of all parent services |

### Additional hook arguments for "before" Hooks

| Property         | Type       | Description              |
| ---------------- | ---------- | ------------------------ |
| `$args->handler` | `callable` | Endpoint request handler |

### Additional hook arguments for "after" Hooks

| Property          | Type     | Description                            |
| ----------------- | -------- | -------------------------------------- |
| `$args->response` | Response | Response from endpoint request handler |

## Hook methods reference

### Before request

| Method                | Description                 |
| --------------------- | --------------------------- |
| `hookBefore()`        | Hook before any request     |
| `hookBeforeGet()`     | Hook before GET request     |
| `hookBeforePost()`    | Hook before POST request    |
| `hookBeforeHead()`    | Hook before HEAD request    |
| `hookBeforePut()`     | Hook before PUT request     |
| `hookBeforeDelete()`  | Hook before DELETE request  |
| `hookBeforeOptions()` | Hook before OPTIONS request |

### After request

| Method               | Description                |
| -------------------- | -------------------------- |
| `hookAfter()`        | Hook after any request     |
| `hookAfterGet()`     | Hook after GET request     |
| `hookAfterPost()`    | Hook after POST request    |
| `hookAfterHead()`    | Hook after HEAD request    |
| `hookAfterPut()`     | Hook after PUT request     |
| `hookAfterDelete()`  | Hook after DELETE request  |
| `hookAfterOptions()` | Hook after OPTIONS request |
