<?php namespace ProcessWire;

use PwJsonApi\{Response, Service};

/**
 * Generic service for building nested service chains in tests
 */
class ChainService extends Service
{
  /**
   * @param ChainService[] $children
   */
  public function __construct(
    protected string $segment,
    protected array $children = [],
  ) {
    parent::__construct();

    // Service names must be unique within an API
    $this->name = 'Chain' . ucfirst($segment);
  }

  protected function init()
  {
    $this->setBasePath('/' . $this->segment);

    $this->addEndpoint('/')->get(function ($args) {
      return new Response([
        'segment' => $this->segment,
      ]);
    });

    $this->addEndpoint('/sibling')->get(function ($args) {
      return new Response([
        'segment' => $this->segment,
      ]);
    });

    foreach ($this->children as $child) {
      $this->addService($child);
    }
  }
}
