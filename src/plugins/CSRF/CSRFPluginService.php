<?php namespace PwJsonApi\Plugins;

use PwJsonApi\{Service, Response};
use PwJsonApi\Plugins\CSRFPlugin;

class CSRFPluginService extends Service
{
  public function __construct(CSRFPlugin $csrfPlugin)
  {
    parent::__construct();

    $this->addEndpoint($csrfPlugin->endpointPath)->get(function () use (
      $csrfPlugin,
    ) {
      return (new Response(null))->with($csrfPlugin->getToken());
    });
  }
}
