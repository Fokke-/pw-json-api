---
description: 'Overview of the full application lifecycle from bootstrapping to request handling and response output.'
---

# Application lifecycle

This page provides a high-level overview of the full application lifecycle, from bootstrapping to response output. Each step links to a detailed page — this page does not repeat their content.

## Bootstrap

The bootstrap phase runs once when the module file is loaded. It sets up the API structure before any HTTP request is handled.

1. **Create an API instance** — `new Api()` creates the top-level container. [Read more about the API instance](/api-instance).
2. **Configure** — `configure()` sets options like `trailingSlashes` and `jsonFlags`. [Read more about configuration](/api-instance#configuration).
3. **Set base path** — `setBasePath()` defines the root path prefix for all endpoints. [Read more about the base path](/api-instance#base-path).
4. **Add services** — `addService()` registers services. Each service's `init()` method is called, which registers endpoints, hooks, and child services. [Read more about services](/services).
5. **Add hooks and plugins** — Request hooks and plugins can be added at the API, service, or endpoint level. [Read more about request hooks](/request-hooks). [Read more about plugins](/plugins/plugins-overview).
6. **Run** — `run()` validates the configuration (no duplicate service names or endpoint paths), registers a [ProcessWire URL hook](https://processwire.com/docs/modules/hooks/#url-hooks-new-in-3-0-173) listener for each endpoint path, and locks the instance to prevent further mutations. [Read more about running the API](/api-instance#running-the-api).

## Request handling

The request handling phase runs for each incoming HTTP request that matches a registered endpoint path.

1. **OPTIONS shortcut** — If the request method is `OPTIONS`, a `200` response with an `Allow` header is returned immediately. No hooks or handlers are executed. [Read more about endpoints](/endpoints).
2. **Request object** — A `Request` object is created containing the HTTP method, path, query parameters, headers, body, and files. [Read more about requests](/requests).
3. **Handler lookup** — The library looks up a handler for the request method. If no handler is registered, a `405` response with an `Allow` header is returned. [Read more about endpoints](/endpoints).
4. **Authentication** — If an authenticator is configured, it runs now. The closest level to the endpoint wins (endpoint > service > API). [Read more about authentication](/authentication-overview).
5. **Authorization** — All authorization callbacks in the chain run in order: API → services → endpoint. [Read more about authorization](/authentication-overview#authorization).
6. **Before hooks** — Before hooks are executed in order: API → service → endpoint. [Read more about hook execution order](/request-hooks#hook-execution-order).
7. **Handler execution** — The endpoint handler runs and returns a `Response`. [Read more about responses](/responses).
8. **After hooks** — After hooks are executed in order: endpoint → service → API. [Read more about hook execution order](/request-hooks#hook-execution-order).
9. **Error handling** — If an exception is thrown at any point, error hooks are executed and the exception is converted to a JSON response. [Read more about error handling](/error-handling). [Read more about error hooks](/error-hooks).
10. **JSON response** — The final response is encoded as JSON and sent to the client.
