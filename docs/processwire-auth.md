---
description: 'Built-in ProcessWire session authenticator with login and logout endpoints.'
---

# ProcessWire authentication <Badge type="tip" text="^2.2" />

A built-in authenticator that uses ProcessWire's session-based authentication. It checks whether the current user is logged in via `$user->isLoggedin()`.

## Setup

`ProcessWireAuth` is the authenticator class. `ProcessWireAuthService` provides login and logout endpoints.

When using session-based authentication, it is recommended to also install the [CSRF plugin](/plugins/csrf) and [Rate Limit plugin](/plugins/rate-limit) on the API instance. The CSRF plugin protects against cross-site request forgery, and the Rate Limit plugin provides brute force protection for all endpoints — including the login endpoint. ProcessWire's built-in `SessionLoginThrottle` [does not activate for JSON requests](#login-throttling).

::: tip Authentication level
Set the authenticator on individual services, not on the API instance. The login and logout endpoints must remain publicly accessible — they cannot be behind authentication.
:::

```php
use PwJsonApi\Api;
use PwJsonApi\Auth\{ProcessWireAuth, ProcessWireAuthService};
use PwJsonApi\Plugins\{CSRFPlugin, RateLimitPlugin};

$api = new Api();

$api->addPlugin(new CSRFPlugin());
$api->addPlugin(new RateLimitPlugin());

// Login and logout endpoints (public)
$api->addService(new ProcessWireAuthService(), function ($service) {
  // Optional: run code after a successful login
  // $service->findEndpoint('/login')?->hookAfterPost(function ($args) {
  //   $args->user->setAndSave('last_login', time());
  // });
});

// Parent service for all protected services (no base path needed)
$api->addService(new MyProtectedService(), function ($service) {
  // Require authentication for this service and all its children
  $service->authenticate(new ProcessWireAuth());

  // Child services inherit authentication from the parent
  $service->addService(new ProductService());
  $service->addService(new OrderService());
});

$api->run();
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

When the `SessionLoginThrottle` module is installed (default in ProcessWire), repeated failed login attempts will result in `429` responses. See [Login throttling](#login-throttling) for important limitations.

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

ProcessWire's `SessionLoginThrottle` module (installed by default) throttles repeated failed login attempts. However, it only activates when the request body is sent as `application/x-www-form-urlencoded` or `multipart/form-data`. Requests with a JSON body — the standard content type for API clients — bypass the throttle entirely.

The [Rate Limit plugin](/plugins/rate-limit) provides reliable rate limiting that works regardless of content type. It is recommended to always install it alongside `ProcessWireAuthService` as shown in the [setup example](#setup).

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
