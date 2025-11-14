<?php

namespace PwJsonApi\Plugins;

use PwJsonApi\Plugins\{ApiPlugin, ApiPluginInterface, CSRFPluginService};
use PwJsonApi\{Api, Service, ApiException, Endpoint};
use ProcessWire\{WireException, WireCSRFException};

/**
 * Provides CSRF token validation and rotation
 *
 * - ProcessWire token handling is utilized
 * - Can be installed for an API instance or Service
 * - Provides an endpoint at /csrf-token for retrieving the current token
 * - Validates the token before every POST, PUT, PATCH, or DELETE request
 * - After a successful request, a new token will be injected into the response
 * - If token validation fails, the request will fail and the current token will be injected into the response
 *
 * @see TODO
 */
class CSRFPlugin extends ApiPlugin implements ApiPluginInterface
{
  /** Token name */
  public string $tokenName = 'pw_json_api_csrf_token';

  /** Key name for the token in responses */
  public string $tokenKey = 'csrf_token';

  /** Endpoint path for retrieving the current token */
  public string $endpointPath = '/csrf-token';

  /** Initialize plugin */
  public function init(Api|Service|Endpoint $context): static
  {
    if (!($context instanceof Api)) {
      throw new WireException('You must install CSRFPlugin for API instance');
    }

    parent::init($context);

    $context
      // Add service for retrieving a token
      ->addService(new CSRFPluginService($this))

      // Add hooks for validating the token
      ->hookBeforePost(function () {
        $this->checkToken();
      })
      ->hookBeforePut(function () {
        $this->checkToken();
      })
      ->hookBeforePatch(function () {
        $this->checkToken();
      })
      ->hookBeforeDelete(function () {
        $this->checkToken();
      })

      // Add hooks for rotating the token
      ->hookAfterPost(function ($args) {
        $args->response->with($this->getToken(true));
      })
      ->hookAfterPut(function ($args) {
        $args->response->with($this->getToken(true));
      })
      ->hookAfterPatch(function ($args) {
        $args->response->with($this->getToken(true));
      })
      ->hookAfterDelete(function ($args) {
        $args->response->with($this->getToken(true));
      });

    return $this;
  }

  /**
   * Get current CSRF token
   *
   * @return array<string, array<string, mixed>>
   */
  public function getToken(bool $reset = false): array
  {
    if ($reset === true) {
      $this->wire->session->CSRF->resetToken($this->tokenName);
    }

    return [
      $this->tokenKey => $this->wire->session->CSRF->getToken($this->tokenName),
    ];
  }

  /**
   * Check CSRF token
   *
   * @throws ApiException If validation fails
   */
  protected function checkToken(): void
  {
    try {
      $this->wire->session->CSRF->validate($this->tokenName);
    } catch (WireCSRFException $e) {
      throw (new ApiException($e->getMessage()))->with($this->getToken());
    }
  }
}
