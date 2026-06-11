---
description: 'Built-in ProcessWire session authenticator with login and logout endpoints.'
---

# ProcessWire authentication <Badge type="tip" text="^2.3" />

A built-in authenticator that uses ProcessWire's session-based authentication. It checks whether the current user is logged in via `$user->isLoggedin()`.

## Setup

`ProcessWireAuth` is the authenticator class. `ProcessWireAuthService` provides login and logout endpoints.

When using session-based authentication with a browser frontend, it is recommended to also install the [CSRF plugin](/plugins/csrf) on the API instance to protect against cross-site request forgery.

::: tip Authentication level
Set the authenticator on individual services, not on the API instance. The login and logout endpoints must remain publicly accessible — they cannot be behind authentication.
:::

```php
use PwJsonApi\Api;
use PwJsonApi\Auth\{ProcessWireAuth, ProcessWireAuthService};
use PwJsonApi\Plugins\CSRFPlugin;

$api = new Api();

$api->addPlugin(new CSRFPlugin());

// Login and logout endpoints (public)
$api->addService(new ProcessWireAuthService());

// This protected service requires authentication
$api->addService(new MyProtectedService());

$api->run();
```

`MyProtectedService` configures authentication in its `init()` method:

```php
use PwJsonApi\Auth\ProcessWireAuth;
use PwJsonApi\{Response, Service};

class MyProtectedService extends Service
{
  protected function init()
  {
    // Require authentication for this service
    $this->authenticate(new ProcessWireAuth());

    $this->addEndpoint('/me')->get(function ($args) {
      return new Response([
        'name' => $args->user->name,
      ]);
    });

    // Child services inherit authentication
    $this->addService(new ProductService());
    $this->addService(new OrderService());
  }
}
```

## Endpoints

`ProcessWireAuthService` registers endpoints under the `/auth` base path.

### POST /auth/login

Authenticates a user with username and password.

**Request body:**

```json
{
  "username": "my-username",
  "password": "my-password"
}
```

**Responses:**

| Status | Description                         |
| ------ | ----------------------------------- |
| 200    | Login successful                    |
| 401    | Invalid credentials                 |
| 429    | Too many attempts (login throttled) |

When the `SessionLoginThrottle` module is installed (default in ProcessWire), repeated failed login attempts will result in `429` responses. See [Login throttling](#login-throttling).

### POST /auth/logout

Ends the current session.

**Responses:**

| Status | Description       |
| ------ | ----------------- |
| 200    | Logout successful |

The logout endpoint does not require authentication — calling it without an active session is a safe no-op.

## CSRF protection

When using session-based authentication with a browser frontend, it is recommended to also enable the [CSRF plugin](/plugins/csrf) to protect against cross-site request forgery.

```php
use PwJsonApi\Plugins\CSRFPlugin;

$api->addPlugin(new CSRFPlugin());
```

In ProcessWire, every user has a session (including guests), so the CSRF token is always available. When the CSRF plugin is installed, it automatically protects all POST endpoints — including login and logout.

## Login throttling

ProcessWire's `SessionLoginThrottle` module (installed by default) is automatically active for all login requests made through `ProcessWireAuthService`, regardless of content type. It throttles repeated failed login attempts by imposing an increasing delay for each attempt.

## Authorization

`ProcessWireAuth` only handles authentication (verifying identity). To restrict access based on user roles or permissions, add [authorization](/authentication-overview#authorization) callbacks to your services or endpoints:

```php
use PwJsonApi\AuthorizeArgs;

$api->addService(new AdminService(), function ($service) {
  $service->authenticate(new ProcessWireAuth());
  $service->authorize(function (AuthorizeArgs $args) {
    return $args->user->isSuperuser();
  });
});
```

## Customizing error responses

Use [error hooks](/error-hooks) to add data to authentication or authorization error responses:

```php
use PwJsonApi\{AuthenticationException, AuthorizationException};

$api->hookOnError(function ($args) {
  if ($args->exception instanceof AuthenticationException) {
    $args->response->with([
      'message' => 'Please log in to access this resource.',
    ]);
  }

  if ($args->exception instanceof AuthorizationException) {
    $args->response->with([
      'message' => 'You do not have permission to access this resource.',
    ]);
  }
});
```
