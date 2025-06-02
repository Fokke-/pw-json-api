# Request hooks

Request hooks can be used to modify the behavior of endpoints. The most common use cases are to check for authorization before the request is handled, or to modify response data after the request has been handled. For this purpose, [hook arguments](#hook-arguments) will be passed to the hook handler function.

The examples below use the `hookBefore()` and `hookAfter()` methods, which apply to any request method. There are also [request type-specific hooks](#hook-methods-reference) available.

## Hook scopes

### API hooks

Defined for the whole API instance. These hooks will apply to all endpoints.

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
  $args->response->data['_foo'] = 'foo';

  // Include additional top-level keys in the response
  $args->response->with([
    'baz' => 'qux',
  ]);
});
```

### Service hooks

Defined for a single service branch. These hooks will apply to all endpoints (including child services) within the given service. Service hooks can be defined directly in the service constructor or injected into the service object.

#### Define in service constructor

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

Defined for a single endpoint. Endpoint hooks can be defined directly when creating an endpoint (which does not make much sense), or they can be injected into the endpoint object.

```php
$api->findEndpoint('/api/hello-world')?->hookAfter(function ($args) {
  $args->response->data['_foo'] = 'foo';
});
```

## Multiple hooks

If you attach multiple hooks on a single target, all of them will be executed.

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
```

## Hook arguments

You can access hook arguments via the `$args` parameter of the hook handler function. The following properties are always included:

| Property          | Type                     | Description                 |
| ----------------- | ------------------------ | --------------------------- |
| `$args->event`    | `\ProcessWire\HookEvent` | ProcessWire URL hook event  |
| `$args->method`   | `string`                 | Request method              |
| `$args->endpoint` | `Endpoint`               | Requested endpoint          |
| `$args->service`  | `Service`                | Requested service           |
| `$args->services` | `ServiceList`            | List of all parent services |

### hookBefore\* arguments

| Property         | Type       | Description              |
| ---------------- | ---------- | ------------------------ |
| `$args->handler` | `callable` | Endpoint request handler |

### hookAfter\* arguments

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
