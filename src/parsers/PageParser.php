<?php

namespace PwJsonApi;

use \ProcessWire\{PageArray, Page, Field};
use function ProcessWire\wire;

class PageParser
{
  protected array $fields = ['id', 'name', 'title'];
  protected array $excludeFields = [];
  protected array $output = [];

  /**
   * @var callable|null
   */
  protected $onParseHandler = null;

  public function __construct() {}

  public function fields(string ...$fields): static
  {
    $this->fields = [...$this->fields, ...$fields];
    return $this;
  }

  public function excludeFields(string ...$excludeFields): static
  {
    $this->excludeFields = [...$this->excludeFields, ...$excludeFields];
    return $this;
  }

  public function onParse(callable $handler): static
  {
    $this->onParseHandler = $handler;
    return $this;
  }

  protected function parsePage(\ProcessWire\Page $page, string ...$fields): array
  {
    // Get fields to parse
    $fields = (function () use ($page, $fields) {
      $fields = array_diff(
        [
          ...$this->fields, // Global fields
          ...$fields, // Fields defined in parse call
        ],
        $this->excludeFields // Excluded fields
      );

      // If no fields are specified, get all
      if (empty($fields)) {
        return $page->getFields()->explode('name');
      }

      return $fields;
    })();

    // TODO: maybe join fields here?

    // Parse page
    $parsedPage = array_reduce(
      $fields,
      function ($acc, $fieldName) use ($page) {
        if (!$page->has($fieldName)) {
          return $acc;
        }

        $acc[$fieldName] = $this->parseField($fieldName, $page);
        return $acc;
      },
      []
    );

    // Run callback
    if (is_callable($this->onParseHandler)) {
      $handlerResult = call_user_func($this->onParseHandler, $parsedPage, $page);
      if (!is_array($handlerResult)) {
        throw new \Exception('You must return array from onParse() callback.');
      }

      $parsedPage = $handlerResult;
    }

    return $parsedPage;
  }

  protected function parsePageArray(PageArray $input, string ...$fields): array
  {
    return array_map(function (Page $page) use ($fields) {
      return $this->parsePage($page, ...$fields);
    }, $input->getArray());
  }

  protected function parseField(string $fieldName, Page $page): mixed
  {
    // If page has this field, parse it
    if ($page->hasField($fieldName)) {
      return $page->{$fieldName};
    }

    // If field name is a property, return value as-is
    if ($page->has($fieldName)) {
      return $page->{$fieldName};
    }

    return null;
  }

  public function parse(PageArray|Page $input, string ...$fields): static
  {
    $this->output = [
      ...$input instanceof PageArray
        ? $this->parsePageArray($input, ...$fields)
        : $this->parsePage($input, ...$fields),
      // ...(function () use ($input, $fields) {
      //   if ($input instanceof PageArray) {
      //     return array_map(function (Page $page) use ($fields) {
      //       return $this->parsePage($page, $fields);
      //     }, $input->getArray());
      //   }

      //   return $this->parsePage($input, $fields);
      // })(),
    ];

    return $this;
  }

  public function toArray()
  {
    return $this->output;
  }

  public function toResponse()
  {
    return new Response($this->output);
  }
}
