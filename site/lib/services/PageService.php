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
        $config->parsePageReferenceChildren = false;
        $config->maxDepth = 3;
        $config->fullFileUrls = true;
      })
      ->input(wire()->pages->findOne(1017)->children('template=basic-page'))
      ->hookBeforePageParse(function ($args) {
        $args->parser->fields('float', 'integer');
      })
      ->hookBeforeFieldParse(function ($args) {
        // For the field named "tags", reconfigure parser
        if (
          $args->page->template->name === 'basic-page' &&
          $args->field->name === 'multiple_pages'
        ) {
          $args->parser->fields('title');
        }
      })
      ->hookAfterFieldParse(function ($args) {
        if ($args->field->name === 'title') {
          $args->parsedValue = ucfirst($args->parsedValue);
        }
      })
      ->hookAfterFileParse(function ($args) {
        if ($args->field->name === 'single_file') {
          $args->parsedFile['_foo'] = 'bar';
        }
      })
      ->toArray();
  }
}
