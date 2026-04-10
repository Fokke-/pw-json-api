---
description: 'Protect API endpoints with authentication and authorization at the API, service, or endpoint level.'
---

# Authentication & authorization

Authentication verifies _who_ the user is. Authorization verifies _what_ the user is allowed to do. These are two independent mechanisms — you can use either or both.

## Authentication

Authentication is configured by passing an `AuthInterface` implementation to the `authenticate()` method. This can be done at three levels: API, service, or endpoint.

```php
use PwJsonApi\Api;

$api = new Api();
$api->authenticate(new PwAuth());
```

When set on a level, **all children inherit it**. If the API instance has an authenticator, every service and endpoint under it is protected — there is no way to opt out. If you need both public and protected endpoints, set the authenticator on specific services instead of the API.

```php
// Only ProtectedService requires authentication
$api->addService(new PublicService());
$api->addService(
  new ProtectedService(),
  fn($s) => $s->authenticate(new PwAuth()),
);
```

### Closest level wins

If multiple levels define an authenticator, the **closest to the endpoint wins** (endpoint > service > API). Authenticators are not chained.

### The `AuthInterface`

All authenticators implement `AuthInterface`. The `authenticate()` method receives an `AuthenticateArgs` DTO and should throw `AuthenticationException` on failure.

```php
use PwJsonApi\{AuthenticateArgs, AuthenticationException, AuthInterface};

class PwAuth implements AuthInterface
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

| Property   | Type                    | Description                |
| ---------- | ----------------------- | -------------------------- |
| `$request` | `Request`               | The current request        |
| `$event`   | `ProcessWire\HookEvent` | ProcessWire URL hook event |

`AuthenticationException` extends `ApiException` and returns a `401` response.

## Authorization

Authorization is configured by passing a callback to the `authorize()` method. The callback receives an `AuthorizeArgs` DTO and returns `bool` — `false` results in a `403` response.

```php
$service->authorize(
  static fn(AuthorizeArgs $args) => $args->user->hasRole('editor'),
);
```

### Authorization is chained

Unlike authentication, **all authorization callbacks in the chain are executed** in order: API → services → endpoint. If any callback returns `false`, the request is rejected with `AuthorizationException` (403).

```php
// API: must be logged in
$api->authorize(static fn(AuthorizeArgs $args) => $args->user->isLoggedin());

// Service: must have editor role
$api->addService(new ContentService(), function ($s) {
  $s->authorize(
    static fn(AuthorizeArgs $args) => $args->user->hasRole('editor'),
  );
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
3. Plugin before hooks
4. Request before hooks
5. Endpoint handler
6. Request after hooks

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
