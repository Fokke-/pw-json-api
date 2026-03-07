<?php

namespace PwJsonApi\Plugins;

use PwJsonApi\Plugins\{ApiPlugin, CSRFPluginService};
use PwJsonApi\{Api, Service, ApiException, Endpoint};
use ProcessWire\{WireException, WireCSRFException};

/**
 * This plugin adds cross-site request forgery (CSRF) protection for your endpoints,
 * utilising ProcessWire’s built-in token validation.
 *
 * @see https://fokke-.github.io/pw-json-api/plugins/csrf.html
 */
class CSRFPlugin extends ApiPlugin
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
    // This is a dirty move, but it's required
    // to allow passing token as a header.
    // Otherwise the plugin would force the end-user to define
    // form payload as FormData.
    $previousAjax = $this->wire->config->ajax;
    $this->wire->config->ajax = true;

    try {
      $this->wire->session->CSRF->validate($this->tokenName);
    } catch (WireCSRFException $e) {
      throw (new ApiException($e->getMessage()))->with($this->getToken());
    } finally {
      $this->wire->config->ajax = $previousAjax;
    }
  }
}
