<?php namespace ProcessWire;

// JSON API
use PwJsonApi\{Api, ApiException, Response};
use PwJsonApi\Auth\{ProcessWireAuth, ProcessWireAuthService, PublicAuth};
use PwJsonApi\Plugins\{CSRFPlugin, RateLimitPlugin};

if (!defined('PROCESSWIRE')) {
  die();
}

if ($page->template->name !== 'admin') {
  (new Api())
    ->configure(function ($config) {
      $config->jsonFlags =
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
    })
    ->setBasePath('/api')
    ->hookBefore(function ($args) {
      if (
        $args->service instanceof HooksService ||
        $args->service instanceof HooksChildService
      ) {
        HooksService::$beforeOrder[] = 'api';
      }
    })
    ->hookAfter(function ($args) {
      if (
        $args->service instanceof HooksService ||
        $args->service instanceof HooksChildService
      ) {
        $args->response->with([
          'after_hook_execution_order' => [
            ...$args->response->additionalData['after_hook_execution_order'] ??
            [],
            'api',
          ],
        ]);
      }

      $args->response->with([
        'request' => $args->request->toArray(),
      ]);
    })
    ->hookOnError(function ($args) {
      if (
        $args->service instanceof ExceptionService ||
        $args->service instanceof HooksService ||
        $args->service instanceof HooksChildService
      ) {
        $args->response->with([
          'error_hook_execution_order' => [
            ...$args->response->additionalData['error_hook_execution_order'] ??
            [],
            'api',
          ],
        ]);
      }

      $args->response->with([
        'request' => $args->request->toArray(),
      ]);
    })
    ->handleException(function ($args) {
      throw (new ApiException())->code(400)->with([
        'message' => $args->exception->getMessage(),
        'request' => $args->request->toArray(),
      ]);
    })
    ->addService(new FoodService(), function ($service) {
      $service->addService(new FruitService());
    })
    ->addService(new PageService())
    ->addService(new HelloWorldService())
    ->addService(new RequestService())
    ->addService(new HooksService())
    ->addService(new ExceptionService())
    ->run();

  (new Api())
    ->setBasePath('exception-response')
    ->handleException(function ($args) {
      return (new Response([
        'handled' => true,
        'message' => $args->exception->getMessage(),
      ]))->code(500);
    })
    ->addService(new ExceptionService())
    ->run();

  (new Api())
    ->setBasePath('plugins')
    ->addPlugin(new TestPlugin())
    ->addService(new RequestService(), function ($service) {
      $service->setBasePath(null);
      $service->addPlugin(new TestPlugin());
      $service->findEndpoint('/')?->addPlugin(new TestPlugin());
    })
    ->run();

  (new Api())
    ->setBasePath('plugin-tree')
    ->addPlugin(new PluginTreeTestPlugin())
    ->addService(new FoodService())
    ->run();

  (new Api())
    ->setBasePath('plugin-hook-attach')
    ->addPlugin(new PluginHookAttachTestPlugin())
    ->addService(new FoodService())
    ->run();

  (new Api())
    ->setBasePath('csrf')
    ->addPlugin(new CSRFPlugin(), function ($plugin) {
      // Token name
      $plugin->tokenName = 'pw_json_api_csrf_token';

      // Token key name in responses
      $plugin->tokenKey = 'csrf_token';

      // Endpoint path for retrieving the current token
      $plugin->endpointPath = '/csrf-token';
    })
    ->hookAfter(function ($args) {
      $args->response->with([
        'request' => $args->request->toArray(),
      ]);
    })
    ->hookOnError(function ($args) {
      $args->response->with([
        'request' => $args->request->toArray(),
      ]);
    })
    ->addService(new CSRFService())
    ->run();

  (new DocumentedApi())
    ->setBasePath('documented-api')
    ->addService(new DocumentedService())
    ->run();

  // Authentication and authorization
  (new Api())
    ->setBasePath('auth-api')
    ->addService(new ProcessWireAuthService())
    ->addService(new AuthService(), function ($service) {
      $service->authenticate(new TestAuth());
    })
    ->hookOnError(function ($args) {
      $args->response->with([
        'exception_class' => get_class($args->exception),
      ]);
    })
    ->run();

  // Authenticator override
  (new Api())
    ->setBasePath('auth-override-api')
    ->authenticate(new TestAuth())
    ->addService(new AuthOverrideService())
    ->addService(new AuthOverridePublicService(), function ($service) {
      $service->authenticate(new PublicAuth());
    })
    ->run();

  // Authentication and authorization argument objects
  (new Api())
    ->setBasePath('auth-context-api')
    ->authenticate(new ContextAuth())
    ->authorize(function ($args) {
      AuthContextChildService::$authorizeArgs = AuthContextChildService::describeArgs(
        $args,
      );
      return true;
    })
    ->addService(new AuthContextService())
    ->run();

  // Skip authentication for built-in services under an API gate
  (new Api())
    ->setBasePath('auth-skip-api')
    ->authenticate(new GateAuth())
    ->addPlugin(new CSRFPlugin(), function ($plugin) {
      $plugin->setupService(function ($service) {
        $service->skipAuthentication();
      });
    })
    ->addService(new ProcessWireAuthService(), function ($service) {
      $service->skipAuthentication();
    })
    ->run();

  // Built-in services follow the API gate by default
  (new Api())
    ->setBasePath('auth-gate-api')
    ->authenticate(new GateAuth())
    ->addPlugin(new CSRFPlugin())
    ->run();

  // Skip authorization
  (new Api())
    ->setBasePath('authz-skip-api')
    ->authorize(function ($args) {
      return false;
    })
    ->addPlugin(new CSRFPlugin(), function ($plugin) {
      $plugin->setupService(function ($service) {
        $service->skipAuthorization();
      });
    })
    ->addService(new AuthSkipService(), function ($service) {
      $service->skipAuthorization();
    })
    ->addService(new HelloWorldService())
    ->run();

  // Authentication opt-out chains (/foo → /foo/bar → /foo/bar/baz)

  // Scenario 1: authenticator on foo, bar skips
  (new Api())
    ->setBasePath('authn-s1-api')
    ->addService(
      (new ChainService('foo', [
        (new ChainService('bar'))->skipAuthentication(),
      ]))->authenticate(new GateAuth()),
    )
    ->run();

  // Scenario 2: authenticator on bar only
  (new Api())
    ->setBasePath('authn-s2-api')
    ->addService(
      new ChainService('foo', [
        (new ChainService('bar'))->authenticate(new GateAuth()),
      ]),
    )
    ->run();

  // Scenario 3: authenticator on bar, baz skips
  (new Api())
    ->setBasePath('authn-s3-api')
    ->addService(
      new ChainService('foo', [
        (new ChainService('bar', [
          (new ChainService('baz'))->skipAuthentication(),
        ]))->authenticate(new GateAuth()),
      ]),
    )
    ->run();

  // Endpoint level: authenticator on foo, its root endpoint skips
  (new Api())
    ->setBasePath('authn-endpoint-api')
    ->addService(
      (new ChainService('foo'))->authenticate(new GateAuth()),
      function ($service) {
        $service->findEndpoint('/')->skipAuthentication();
      },
    )
    ->run();

  // Authorization opt-out chains (/foo → /foo/bar → /foo/bar/baz)

  // Scenario 1: authorizer on foo, bar skips
  (new Api())
    ->setBasePath('authz-s1-api')
    ->addService(
      (new ChainService('foo', [
        (new ChainService('bar'))->skipAuthorization(),
      ]))->authorize(function ($args) {
        return false;
      }),
    )
    ->run();

  // Scenario 2: authorizer on bar only
  (new Api())
    ->setBasePath('authz-s2-api')
    ->addService(
      new ChainService('foo', [
        (new ChainService('bar'))->authorize(function ($args) {
          return false;
        }),
      ]),
    )
    ->run();

  // Scenario 3: authorizer on bar, baz skips
  (new Api())
    ->setBasePath('authz-s3-api')
    ->addService(
      new ChainService('foo', [
        (new ChainService('bar', [
          (new ChainService('baz'))->skipAuthorization(),
        ]))->authorize(function ($args) {
          return false;
        }),
      ]),
    )
    ->run();

  // Endpoint level: authorizer on foo, its root endpoint skips
  (new Api())
    ->setBasePath('authz-endpoint-api')
    ->addService(
      (new ChainService('foo'))->authorize(function ($args) {
        return false;
      }),
      function ($service) {
        $service->findEndpoint('/')->skipAuthorization();
      },
    )
    ->run();

  // Override: opt-out combined with a rule on the same level

  // Authentication: parent authenticator is replaced
  (new Api())
    ->setBasePath('authn-override-a-api')
    ->addService(
      (new ChainService('foo', [
        (new ChainService('bar'))
          ->skipAuthentication()
          ->authenticate(new PublicAuth()),
      ]))->authenticate(new GateAuth()),
    )
    ->run();

  // Authentication: own authenticator still runs
  (new Api())
    ->setBasePath('authn-override-b-api')
    ->addService(
      new ChainService('foo', [
        (new ChainService('bar'))
          ->skipAuthentication()
          ->authenticate(new GateAuth()),
      ]),
    )
    ->run();

  // Authorization: parent authorizer is replaced
  (new Api())
    ->setBasePath('authz-override-a-api')
    ->addService(
      (new ChainService('foo', [
        (new ChainService('bar'))
          ->skipAuthorization()
          ->authorize(function ($args) {
            return true;
          }),
      ]))->authorize(function ($args) {
        return false;
      }),
    )
    ->run();

  // Authorization: own authorizer still runs
  (new Api())
    ->setBasePath('authz-override-b-api')
    ->addService(
      (new ChainService('foo', [
        (new ChainService('bar'))
          ->skipAuthorization()
          ->authorize(function ($args) {
            return false;
          }),
      ]))->authorize(function ($args) {
        return true;
      }),
    )
    ->run();

  // On-request hooks
  (new Api())
    ->setBasePath('on-request-api')
    ->hookOnRequest(function ($args) {
      $args->headers['X-On-Request'] = 'api';
      $args->headers['X-Order'] = 'api';
    })
    ->hookOnError(function ($args) {
      $args->response->with([
        'error_hook' => true,
      ]);
    })
    ->addService(new OnRequestService())
    ->addService((new ChainService('gated'))->authenticate(new GateAuth()))
    ->run();

  // ProcessWireAuth (mirrors docs/processwire-auth.md setup example)
  (new Api())
    ->setBasePath('pw-auth-api')
    ->addService(new ProcessWireAuthService())
    ->addService(new PwAuthProtectedService())
    ->run();

  // Response headers
  (new Api())
    ->setBasePath('response-headers')
    ->hookAfter(function ($args) {
      $args->response->header('X-After-Hook-Header', 'after-hook-value');
    })
    ->addService(new ResponseHeaderService())
    ->run();

  // Rate limit
  (new Api())
    ->setBasePath('rate-limit-api')
    ->addPlugin(new RateLimitPlugin(), function ($plugin) {
      $plugin->limit = 3;
      $plugin->window = 60;
    })
    ->addService(new RateLimitService())
    ->run();
}
