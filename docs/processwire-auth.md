---
description: 'Built-in ProcessWire session authenticator with login and logout endpoints.'
---

# ProcessWire authentication <Badge type="tip" text="^2.3" />

A built-in authenticator that uses ProcessWire's session-based authentication. It checks whether the current user is logged in via `$user->isLoggedin()`.

## Setup

`ProcessWireAuth` is the authenticator class. `ProcessWireAuthService` provides login and logout endpoints.

When using session-based authentication with a browser frontend, it is recommended to also install the [CSRF plugin](/plugins/csrf) on the API instance to protect against cross-site request forgery.

::: tip Authentication level
The login and logout endpoints must be reachable without a ProcessWire session. Set the authenticator on individual services as shown below, or [set it on the API instance](#authentication-on-the-api-level) and opt the login and logout endpoints out explicitly.
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

    // Optional: require specific role
    // $this->authorize(function ($args) {
    //   return $args->user->hasRole('editor');
    // });

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

## Authentication on the API level <Badge type="tip" text="^2.5" />

To protect the whole API, set the authenticator on the API instance and opt out the endpoints that must be reachable without a session: the login and logout endpoints, and the [CSRF token endpoint](/plugins/csrf#configuring-the-token-endpoint) if the CSRF plugin is installed.

```php
use PwJsonApi\Api;
use PwJsonApi\Auth\{ProcessWireAuth, ProcessWireAuthService};
use PwJsonApi\Plugins\CSRFPlugin;

$api = new Api();

// Every service requires authentication...
$api->authenticate(new ProcessWireAuth());

// ...except the CSRF token endpoint
$api->addPlugin(new CSRFPlugin(), function ($plugin) {
  $plugin->setupService(function ($service) {
    $service->skipAuthentication();
  });
});

// ...and the login and logout endpoints
$api->addService(new ProcessWireAuthService(), function ($service) {
  $service->skipAuthentication();
});

$api->addService(new MyProtectedService());

$api->run();
```

`ProcessWireAuthService` never opts out on its own — it always follows the rules you set. If the API authenticator is a different kind of gate, such as an API key check, the login endpoint should normally stay behind it. In that case, keep the gate on the API and set `ProcessWireAuth` on the protected services instead.

Opting out of authentication does not affect [authorization](#authorization). If the API instance has authorization callbacks, they still apply to the login and logout endpoints. Use `skipAuthorization()` to opt out of them as well. See [Opting out](/authentication-overview#opting-out) for details.

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

In ProcessWire, every user has a session (including guests), so the CSRF token is always available. When the CSRF plugin is installed, it automatically protects all POST endpoints — including login and logout. If the authenticator is set on the API instance, opt the token endpoint out so that guests can retrieve a token before logging in (see [Authentication on the API level](#authentication-on-the-api-level)).

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
