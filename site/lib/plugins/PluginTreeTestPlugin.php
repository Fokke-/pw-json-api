<?php

namespace ProcessWire;

use PwJsonApi\Plugins\ApiPlugin;
use PwJsonApi\{Api, Service, Endpoint};

class PluginTreeTestPlugin extends ApiPlugin
{
  public function init(Api|Service|Endpoint $context): static
  {
    parent::init($context);

    $foodService = $context->findService('FoodService');
    $vegetableService = $context->findService('VegetableService');

    // Find endpoints on discovered services
    $foodEndpoint = $foodService?->findEndpoint('/');
    $vegetableEndpoint = $vegetableService?->findEndpoint('/carrot');

    $context->hookAfter(function ($args) use (
      $foodService,
      $vegetableService,
      $foodEndpoint,
      $vegetableEndpoint,
    ) {
      // Plugin found a direct child service
      if (
        $foodService !== null &&
        in_array($foodService, $args->services->getItems())
      ) {
        $args->response->with([
          'plugin_found_service' => true,
        ]);
      }

      // Plugin found a nested child service (FoodService -> VegetableService)
      if (
        $vegetableService !== null &&
        in_array($vegetableService, $args->services->getItems())
      ) {
        $args->response->with([
          'plugin_found_child_service' => true,
        ]);
      }

      // Plugin found an endpoint on a direct child service
      if ($foodEndpoint !== null && $args->endpoint === $foodEndpoint) {
        $args->response->with([
          'plugin_found_service_endpoint' => true,
        ]);
      }

      // Plugin found an endpoint on a nested child service
      if (
        $vegetableEndpoint !== null &&
        $args->endpoint === $vegetableEndpoint
      ) {
        $args->response->with([
          'plugin_found_child_service_endpoint' => true,
        ]);
      }
    });

    return $this;
  }
}
