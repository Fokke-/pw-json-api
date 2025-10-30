<?php namespace ProcessWire;

use PwJsonApi\Response;
use PwJsonApi\Service;
use PwJsonApi\PageParser;

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
        $config->parseFileCustomFields = true;
        $config->parsePageReferenceChildren = true;
      })
      ->input($this->wire->pages->findOne(1017))
      ->toArray();
  }
}
