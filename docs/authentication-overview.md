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

|                       | Authentication                          | Authorization                                   |
| --------------------- | --------------------------------------- | ----------------------------------------------- |
| Configured with       | `authenticate()`                        | `authorize()`                                   |
| When multiple levels  | Closest wins (endpoint > service > API) | All run (API → services → endpoint)             |
| Child level overrides | By setting its own authenticator        | With `skipAuthorization()` and its own callback |
| Child level opts out  | `skipAuthentication()`                  | `skipAuthorization()`                           |
| Failure               | `AuthenticationException` (401)         | `AuthorizationException` (403)                  |

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

Unlike authentication, **all authorization callbacks in the chain are executed** in order: API → services → endpoint. If any callback returns `false`, the request is rejected. Setting a callback on a child level does not replace the callbacks of the levels above it — to replace them, the child level must [opt out](#opting-out) of them first.

```php
// API: must have member role
$api->authorize(function (AuthorizeArgs $args) {
  return $args->user->hasRole('member');
});

// Service: must also have editor role
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

There are two ways to combine public and protected endpoints in the same API. Start from the first one, and move on only if it does not fit your structure.

### Protect specific services

The simplest approach is to set the authenticator only on the services that need it, instead of the API:

```php
// Only MyProtectedService requires authentication
$api->addService(new MyPublicService());
$api->addService(new MyProtectedService(), function ($service) {
  $service->authenticate(new ExampleAuth());
});
```

### Opting out <Badge type="tip" text="^2.5" />

If most of the API is protected, set the rules on the API and opt specific services or endpoints out of them:

- `skipAuthentication()` skips the authenticators inherited from parent levels.
- `skipAuthorization()` skips the authorization callbacks inherited from parent levels.

```php
// This service is public despite the API authenticator
$api->addService(new MyPublicService(), function ($service) {
  $service->skipAuthentication();
});

// In service init(): this endpoint ignores the parent authorization callbacks
$this->addEndpoint('/status')
  ->get(function () {
    return new Response(['status' => 'ok']);
  })
  ->skipAuthorization();
```

Opting out follows these rules:

- **Only inherited rules are skipped.** An authenticator or authorization callback set on the same level or below still applies.
- **The two are independent.** Skipping authentication does not skip authorization, and vice versa.
- **The whole subtree is affected.** All services and endpoints under the level inherit the opt-out. A child level cannot undo it, but it can set its own authenticator or authorization callback.

For example, with nested services `/foo` → `/foo/bar` → `/foo/bar/baz`:

| Path           | Configuration         | Callbacks run for a request | Why                                           |
| -------------- | --------------------- | --------------------------- | --------------------------------------------- |
| `/foo`         | `authorize()`         | `/foo`                      | Own callback                                  |
| `/foo/bar`     | `skipAuthorization()` | None                        | Skips `/foo`, has no own callback             |
| `/foo/bar/baz` | `authorize()`         | `/foo/bar/baz` only         | Own callback, `/foo` is skipped by `/foo/bar` |

Combining an opt-out with a rule on the same level replaces the parent level rules:

```php
// Only editors, regardless of the parent level callbacks
$api->addService(new EditorService(), function ($service) {
  $service->skipAuthorization()->authorize(function (AuthorizeArgs $args) {
    return $args->user->hasRole('editor');
  });
});
```

::: warning
Skipping authorization also removes parent level restrictions such as role or IP checks from the whole subtree. Use it only when the endpoints are meant to be accessible without them.
:::

#### `PublicAuth` <Badge type="tip" text="^2.4" />

Alternatively, you can set the built-in `PublicAuth` authenticator, which accepts every request. Because the closest authenticator wins, it replaces the inherited one. Unlike `skipAuthentication()`, it can be extended, for example to log or track requests to public endpoints.

```php
use PwJsonApi\Auth\PublicAuth;

$api->addService(new MyPublicService(), function ($service) {
  $service->authenticate(new PublicAuth());
});
```

## Best practices

**Keep authentication and authorization separate.** An authenticator verifies who the user is, for example a logged-in session or an API key. An authorization callback verifies what the user is allowed to do, for example a role. Do not check whether the user is logged in in an authorization callback.

**Protect by default, open explicitly.** Set the rules on the highest level that fits, and make exceptions with [opting out](#opting-out). New services are then protected automatically, and every public service or endpoint is a deliberate choice.

**Make exceptions where the service or endpoint is defined.** For a single service or endpoint, use an opt-out or an own rule (`skipAuthorization()->authorize(...)`) when adding the service or defining the endpoint. Do not add an `instanceof` branch for a single service to a parent level callback. The exception is then visible where the service is defined, and the parent level callback does not grow with every exception.

**Consider whether a subtree needs its own rule.** `skipAuthorization()` alone removes all parent level restrictions from the subtree. Often the right choice is to replace them with a rule of its own instead.

**Built-in services follow your rules.** The [ProcessWire authentication](/processwire-auth#authentication-on-the-api-level) service and the [CSRF plugin](/plugins/csrf#configuring-the-token-endpoint) token endpoint never opt out on their own. If the API instance has rules, opt them out explicitly where needed.

## Execution order

Authentication and authorization run **before** request hooks and plugins:

1. **Authenticate** — closest authenticator runs, unless skipped
2. **Authorize** — all authorization callbacks in the chain run (API → services → endpoint), except the ones skipped
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
