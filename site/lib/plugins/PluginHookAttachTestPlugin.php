<?php

namespace ProcessWire;

use PwJsonApi\Plugins\ApiPlugin;
use PwJsonApi\{Api, Service, Endpoint};

class PluginHookAttachTestPlugin extends ApiPlugin
{
  public function init(Api|Service|Endpoint $context): static
  {
    parent::init($context);

    $foodService = $context->findService('FoodService');
    $vegetableService = $context->findService('VegetableService');

    $foodEndpoint = $foodService?->findEndpoint('/');
    $vegetableEndpoint = $vegetableService?->findEndpoint('/carrot');

    // Hook after direct child service
    $foodService?->hookAfter(function ($args) {
      $args->response->with([
        'plugin_service_hook' => true,
      ]);
    });

    // Hook after nested child service
    $vegetableService?->hookAfter(function ($args) {
      $args->response->with([
        'plugin_child_service_hook' => true,
      ]);
    });

    // Hook after endpoint on direct child service
    $foodEndpoint?->hookAfter(function ($args) {
      $args->response->with([
        'plugin_endpoint_hook' => true,
      ]);
    });

    // Hook after endpoint on nested child service
    $vegetableEndpoint?->hookAfter(function ($args) {
      $args->response->with([
        'plugin_child_endpoint_hook' => true,
      ]);
    });

    return $this;
  }
}
