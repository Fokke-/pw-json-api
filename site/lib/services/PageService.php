<?php namespace ProcessWire;

use PwJsonApi\Response;
use PwJsonApi\Service;
use PwJsonApi\PageParser;
use function ProcessWire\wire;

class PageService extends Service
{
  public function __construct()
  {
    parent::__construct();

    $this->setBasePath('/pages');
    $this->addEndpoint('/')->get(function () {
      return new Response($this->getPages());
    });
  }

  public function getPages(): array
  {
    return (new PageParser())
      ->configure(function ($config) {
        $config->parseChildren = true;
        $config->parsePageFieldChildren = false;
        $config->maxDepth = 3;
        $config->fullFileUrls = true;
      })
      ->input(wire()->pages->findOne(1017)->children('template=basic-page'))
      ->toArray();
  }
}
