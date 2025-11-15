# CSRF protection <Badge type="tip" text="^1.2" />

This plugin adds [cross-site request forgery (CSRF)](https://developer.mozilla.org/en-US/docs/Web/Security/Attacks/CSRF) protection for your endpoints, utilising ProcessWire’s built-in token validation.

The plugin hooks into every `POST`, `PUT`, `PATCH`, or `DELETE` request and expects a valid token to be included in the request payload. Any successful request of those types triggers token rotation, and the new token is included in the response. If validation fails, an `ApiException` is thrown with the current token included.

The plugin also exposes a `/csrf-token` endpoint, which can be used to retrieve the current token.

## Installation

Install the plugin by calling the `addPlugin()` method of the `Api` instance.

```php
use PwJsonApi\Plugins\CSRFPlugin;

$api->addPlugin(new CSRFPlugin());
```

You can access the newly added plugin in an optional setup callback to configure it:

```php
$api->addPlugin(new CSRFPlugin(), function ($plugin) {
  // Token name
  $plugin->tokenName = 'pw_json_api_csrf_token';

  // Key name for the token in responses
  $plugin->tokenKey = 'csrf_token';

  // Endpoint path for retrieving the current token
  $plugin->endpointPath = '/csrf-token';
});
```

## Front-end implementation

Front-end implementation is outside the scope of this guide, but here is a basic flow:

1. Implement a way to store the current token (e.g. in memory, or a state manager).
2. Before any `POST`, `PUT`, `PATCH`, or `DELETE` request, check whether a current token is available. If not, query the `/csrf-token` endpoint to retrieve and store it.
3. Pass the token as a header
4. If the response is successful, read the new token from the response data using the `csrf_token` key and update your store.

```js
const token = {
  name: 'TOKEN1727703112X1763204571',
  value: 'mw4aFl6fFDoE58I90hn.oL7SQQWoAbA7',
};

const response = await fetch('https://example.com/my-post-endpoint', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',

    // Trigger ProcessWire $config->ajax.
    // Required to allow passing token as a header
    'X-Requested-With': 'XMLHttpRequest',

    // CSRF token
    [`X-${token.name}`]: token.value,
  },
  body: JSON.stringify({
    foo: 'bar',
  }),
});

console.warn(await response.json());
```
