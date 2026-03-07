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

    // Find a direct child service and attach a hook
    $foodService?->hookAfter(function ($args) {
      $args->response->with([
        'plugin_found_service' => true,
      ]);
    });

    // Find a nested child service (FoodService → VegetableService)
    $vegetableService?->hookAfter(function ($args) {
      $args->response->with([
        'plugin_found_child_service' => true,
      ]);
    });

    // Find an endpoint on a direct child service
    $foodService?->findEndpoint('/')?->hookAfter(function ($args) {
      $args->response->with([
        'plugin_found_service_endpoint' => true,
      ]);
    });

    // Find an endpoint on a nested child service
    $vegetableService?->findEndpoint('/carrot')?->hookAfter(function ($args) {
      $args->response->with([
        'plugin_found_child_service_endpoint' => true,
      ]);
    });

    return $this;
  }
}
