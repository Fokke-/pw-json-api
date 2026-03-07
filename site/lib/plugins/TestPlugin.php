<?php

namespace ProcessWire;

use PwJsonApi\Plugins\ApiPlugin;
use PwJsonApi\{Api, Service, Endpoint};

class TestPlugin extends ApiPlugin
{
  public function init(Api|Service|Endpoint $context): static
  {
    parent::init($context);

    $context->hookAfter(function ($args) use ($context) {
      $key = (function () use ($context): string|null {
        if ($context instanceof Api) {
          return 'api_plugin';
        }

        if ($context instanceof Service) {
          return 'service_plugin';
        }

        if ($context instanceof Endpoint) {
          return 'endpoint_plugin';
        }

        return null;
      })();

      if (!empty($key)) {
        $args->response->with([
          $key => true,
        ]);
      }
    });

    return $this;
  }
}
