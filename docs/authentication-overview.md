---
description: 'Protect API endpoints with authentication and authorization at the API, service, or endpoint level.'
---

# Authentication & authorization <Badge type="tip" text="^2.2" />

Authentication verifies _who_ the user is. Authorization verifies _what_ the user is allowed to do. These are two independent mechanisms — you can use either or both.

::: tip Looking for a ready-made solution?
See [ProcessWire authentication](/processwire-auth) for a built-in authenticator and login/logout service that uses ProcessWire's session system.
:::

## Authentication

Authentication is configured by passing an `Authenticator` subclass to the `authenticate()` method. This can be done at three levels: API, service, or endpoint.

```php
use PwJsonApi\Api;

$api = new Api();
$api->authenticate(new ExampleAuth());
```

When set on a level, **all children inherit it**. If the API instance has an authenticator, every service and endpoint under it is protected — there is no way to opt out. If you need both public and protected endpoints, set the authenticator on specific services instead of the API.

```php
// Only MyProtectedService requires authentication
$api->addService(new MyPublicService());
$api->addService(new MyProtectedService(), function ($service) {
  $service->authenticate(new ExampleAuth());
});
```

### Closest level wins

If multiple levels define an authenticator, the **closest to the endpoint wins** (endpoint > service > API). Authenticators are not chained.

### The `Authenticator` class

All authenticators extend the abstract `Authenticator` class. It provides access to the ProcessWire API via `$this->wire` (like `ApiPlugin`). The `authenticate()` method receives an `AuthenticateArgs` object and should throw `AuthenticationException` on failure.

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

`AuthenticateArgs` contains:

| Property   | Type                    | Description                  |
| ---------- | ----------------------- | ---------------------------- |
| `$request` | `Request`               | The current request          |
| `$user`    | `ProcessWire\User`      | The current ProcessWire user |
| `$event`   | `ProcessWire\HookEvent` | ProcessWire URL hook event   |

`AuthenticationException` extends `ApiException` and returns a `401` response.

## Authorization

Authorization is configured by passing a callback to the `authorize()` method. The callback receives an `AuthorizeArgs` object and returns `bool` — `false` results in a `403` response.

```php
$service->authorize(function (AuthorizeArgs $args) {
  return $args->user->hasRole('editor');
});
```

### Authorization is chained

Unlike authentication, **all authorization callbacks in the chain are executed** in order: API → services → endpoint. If any callback returns `false`, the request is rejected with `AuthorizationException` (403).

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

### `AuthorizeArgs`

| Property   | Type                    | Description                  |
| ---------- | ----------------------- | ---------------------------- |
| `$request` | `Request`               | The current request          |
| `$user`    | `ProcessWire\User`      | The current ProcessWire user |
| `$event`   | `ProcessWire\HookEvent` | ProcessWire URL hook event   |

## Execution order

Authentication and authorization run **before** plugins and request hooks:

1. **Authenticate** — closest authenticator runs
2. **Authorize** — all authorizers in chain run (API → services → endpoint)
3. Before hooks
4. Endpoint handler
5. After hooks

## Exceptions

Both `AuthenticationException` and `AuthorizationException` extend `ApiException`, so they can be caught in [error hooks](/error-hooks):

```php
use PwJsonApi\{AuthenticationException, AuthorizationException};

$api->hookOnError(function ($args) {
  if ($args->exception instanceof AuthenticationException) {
    $args->response->with(['login_url' => '/login']);
  }
});
```
