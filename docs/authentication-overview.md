---
description: 'Protect API endpoints with authentication and authorization at the API, service, or endpoint level.'
---

# Authentication & authorization <Badge type="tip" text="^2.3" />

Authentication verifies _who_ the user is. Authorization verifies _what_ the user is allowed to do. These are two independent mechanisms — you can use either or both.

::: tip Looking for a ready-made solution?
See [ProcessWire authentication](/processwire-auth) for a built-in authenticator and login/logout service that uses ProcessWire's session system.
:::

## How levels work

Both mechanisms can be configured on three levels: API, service, or endpoint. A level applies to all of its children, but the two mechanisms combine levels differently:

|                          | Authentication                          | Authorization                       |
| ------------------------ | --------------------------------------- | ----------------------------------- |
| Configured with          | `authenticate()`                        | `authorize()`                       |
| When multiple levels     | Closest wins (endpoint > service > API) | All run (API → services → endpoint) |
| Child level can override | Yes                                     | No                                  |
| Failure                  | `AuthenticationException` (401)         | `AuthorizationException` (403)      |

## Authentication

### Creating an authenticator

Authenticators extend the abstract `Authenticator` class. It provides access to the ProcessWire API via `$this->wire` (like `ApiPlugin`). The `authenticate()` method receives an [`AuthenticateArgs`](#argument-objects) object and should throw `AuthenticationException` on failure.

```php
use PwJsonApi\{AuthenticateArgs, AuthenticationException, Authenticator};

class ExampleAuth extends Authenticator
{
  public function authenticate(AuthenticateArgs $args): void
  {
    if ($this->wire->user->isLoggedin() === false) {
      throw new AuthenticationException();
    }
  }
}
```

### Setting an authenticator

Pass an authenticator instance to the `authenticate()` method of the API, a service, or an endpoint:

```php
use PwJsonApi\Api;

$api = new Api();
$api->authenticate(new ExampleAuth());
```

All services and endpoints under the level inherit the authenticator.

### Authenticator is not chained

If multiple levels define an authenticator, the **closest to the endpoint wins** (endpoint > service > API). Only that authenticator runs — the authenticators on the levels above it are skipped. This allows you to use a different authentication method for a part of the API, or to [make it public](#opting-out).

## Authorization

Authorization is configured by passing a callback to the `authorize()` method. The callback receives an [`AuthorizeArgs`](#argument-objects) object and returns `bool` — `false` results in a `403` response.

```php
use PwJsonApi\AuthorizeArgs;

$service->authorize(function (AuthorizeArgs $args) {
  return $args->user->hasRole('editor');
});
```

### Authorization is chained

Unlike authentication, **all authorization callbacks in the chain are executed** in order: API → services → endpoint. If any callback returns `false`, the request is rejected. A child level cannot override the callbacks of the levels above it.

```php
// API: must be logged in
$api->authorize(function (AuthorizeArgs $args) {
  return $args->user->isLoggedin();
});

// Service: must have editor role
$api->addService(new ContentService(), function ($service) {
  $service->authorize(function (AuthorizeArgs $args) {
    return $args->user->hasRole('editor');
  });
});

// Both callbacks run: first API, then service
```

## Argument objects

Authenticators receive an `AuthenticateArgs` object and authorization callbacks receive an `AuthorizeArgs` object. Both contain the same properties:

| Property    | Type                    | Description                                                  |
| ----------- | ----------------------- | ------------------------------------------------------------ |
| `$request`  | `Request`               | The current request                                          |
| `$user`     | `ProcessWire\User`      | The current ProcessWire user                                 |
| `$event`    | `ProcessWire\HookEvent` | ProcessWire URL hook event                                   |
| `$endpoint` | `Endpoint`              | Requested endpoint <Badge type="tip" text="^2.4" />          |
| `$service`  | `Service`               | Requested service <Badge type="tip" text="^2.4" />           |
| `$services` | `ServiceList`           | List of all parent services <Badge type="tip" text="^2.4" /> |
| `$api`      | `Api`                   | API instance <Badge type="tip" text="^2.4" />                |

## Public and protected endpoints

There are three ways to combine public and protected endpoints in the same API. Start from the first one, and move on only if it does not fit your structure.

### Protect specific services

The simplest approach is to set the authenticator only on the services that need it, instead of the API:

```php
// Only MyProtectedService requires authentication
$api->addService(new MyPublicService());
$api->addService(new MyProtectedService(), function ($service) {
  $service->authenticate(new ExampleAuth());
});
```

### Opting out <Badge type="tip" text="^2.4" />

If most of the API is protected, set the authenticator on the API and opt specific services or endpoints out with the built-in `PublicAuth` authenticator. It accepts every request, and because the closest authenticator wins, it replaces the inherited one:

```php
use PwJsonApi\Auth\PublicAuth;
```

```php
// In service init(): this endpoint is public despite the API authenticator
$this->addEndpoint('/status')
  ->get(function () {
    return new Response(['status' => 'ok']);
  })
  ->authenticate(new PublicAuth());
```

`PublicAuth` can also be extended, for example to log or track requests to public endpoints.

::: warning
Opting out only affects authentication. Authorization callbacks are [chained](#authorization-is-chained) and always run, so a callback set on a parent level still applies to the opted-out endpoint. Use [conditional rules](#conditional-rules) to exempt it.
:::

### Conditional rules <Badge type="tip" text="^2.4" />

Authenticators and authorization callbacks receive the requested endpoint and service in their [argument objects](#argument-objects), so a single parent level rule can let specific requests through. This is the only way to exempt requests from a parent level authorization callback:

```php
// API: must be logged in, except for PublicContentService
$api->authorize(function (AuthorizeArgs $args) {
  if ($args->service instanceof PublicContentService) {
    return true;
  }

  return $args->user->isLoggedin();
});
```

`$service` is the service the endpoint belongs to. For nested services, use `$services` to inspect the parent services.

Prefer `instanceof` checks over comparing paths or names as strings. A string comparison silently stops matching when a path or name changes.

::: tip When to use which

- **Protect specific services** when public and protected endpoints are already in separate services.
- **Opting out** when a single service or endpoint should be public. The exception is visible where the service or endpoint is defined.
- **Conditional rules** when a rule spans many services or endpoints, or when you need to bypass a parent level authorization callback.

:::

## Execution order

Authentication and authorization run **before** request hooks and plugins:

1. **Authenticate** — closest authenticator runs
2. **Authorize** — all authorization callbacks in the chain run (API → services → endpoint)
3. Before hooks, including hooks registered by [plugins](/plugins/plugins-overview)
4. Endpoint handler
5. After hooks

See [Application lifecycle](/lifecycle#request-handling) for the full sequence.

## Exceptions

Both `AuthenticationException` (401) and `AuthorizationException` (403) extend `ApiException`, so they can be caught in [error hooks](/error-hooks):

```php
use PwJsonApi\{AuthenticationException, AuthorizationException};

$api->hookOnError(function ($args) {
  if ($args->exception instanceof AuthenticationException) {
    $args->response->with(['login_url' => '/login']);
  }
});
```
