<?php

namespace PwJsonApi\Plugins;

use PwJsonApi\Plugins\{ApiPlugin, CSRFPluginService};
use PwJsonApi\{Api, Service, ApiException, Endpoint};
use ProcessWire\WireException;

/**
 * This plugin adds cross-site request forgery (CSRF) protection for your endpoints,
 * utilising ProcessWire’s built-in token validation.
 *
 * @see https://pwjsonapi.fokke.fi/plugins/csrf.html
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
      throw new WireException(
        'CSRFPlugin can only be installed on an Api instance',
      );
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
    // Required to allow passing the token as a header.
    // ProcessWire only checks the X-{tokenName} header
    // when config->ajax is true.
    $previousAjax = $this->wire->config->ajax;
    $this->wire->config->ajax = true;

    try {
      if (!$this->wire->session->CSRF->hasValidToken($this->tokenName)) {
        throw (new ApiException(
          $this->wire->session->CSRF->_(
            'This request was aborted because it appears to be forged.',
          ),
        ))->with($this->getToken());
      }
    } finally {
      $this->wire->config->ajax = $previousAjax;
    }
  }
}
