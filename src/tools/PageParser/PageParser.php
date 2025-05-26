<?php

namespace PwJsonApi;

use \ProcessWire\{PageArray, Page, Field};

// TODO: field parse hooks
// TODO: all hooks as an array
class PageParser
{
  /**
   * Fields names to parse
   *
   * @var string[]
   */
  protected array $fields = [];

  /**
   * Fields names to exclude
   *
   * @var string[]
   */
  protected array $excludeFields = [];

  /**
   * Data to parse
   */
  protected Page|PageArray|null $input = null;

  /**
   * Handler for before parse page
   *
   * @var callable|null
   */
  protected $beforeParsePageHandler = null;

  /**
   * Handler for after parse page
   *
   * @var callable|null
   */
  protected $afterParsePageHandler = null;

  /**
   * Set Page of PageArray to parse
   *
   * This method can be ran multiple times, and the new payload
   * will be merged with the previous one.
   */
  public function parse(PageArray|Page $input): static
  {
    // If there's no current input, init it an bail out
    if ($this->input === null) {
      $this->input = $input;
      return $this;
    }

    // If current input is Page, and we're inserting another Page or PageArray,
    // convert input to PageArray
    if ($this->input instanceof Page) {
      $this->input = (new PageArray())->add($this->input);
    }

    $this->input->add($input);
    return $this;
  }

  /**
   * Sort pages in input
   *
   * Note that this performs internal sorting using WireArray::sort(), which is slower than
   * sorting pages in query phase. This method is only useful when you have called
   * parse() method multiple times, and the sorting of resulting input is incorrect.
   *
   * @see https://processwire.com/api/ref/wire-array/sort/
   */
  public function sort(string $properties): static
  {
    if ($this->input instanceof PageArray) {
      $this->input->sort($properties);
    }

    return $this;
  }

  /**
   * Specify fields to parse
   *
   * This method can be ran multiple times, and the new payload
   * will be merged with the previous one.
   */
  public function fields(string ...$fields): static
  {
    $this->fields = array_unique([...$this->fields, ...$fields]);
    return $this;
  }

  /**
   * Specify fields to exclude
   *
   * This method can be ran multiple times, and the new payload
   * will be merged with the previous one.
   */
  public function excludeFields(string ...$excludeFields): static
  {
    $this->excludeFields = array_unique([...$this->excludeFields, ...$excludeFields]);
    return $this;
  }

  /**
   * Hook to run before a single page will be parsed
   *
   * @param callable(HookReturnBeforePageParse): void $handler
   */
  public function beforeParse(callable $handler): static
  {
    $this->beforeParsePageHandler = $handler;
    return $this;
  }

  /**
   * Hook to run after a single page has been parsed
   *
   * @param callable(HookReturnAfterPageParse): void $handler
   */
  public function afterParse(callable $handler): static
  {
    $this->afterParsePageHandler = $handler;
    return $this;
  }

  /**
   * Parse a single page
   */
  protected function parsePage(\ProcessWire\Page $page): array
  {
    // Run beforeParsePage hook
    if (is_callable($this->beforeParsePageHandler)) {
      $hookRet = new HookReturnBeforePageParse();
      $hookRet->page = $page;

      call_user_func($this->beforeParsePageHandler, $hookRet);
      $page = $hookRet->page;
    }

    // Gather fields to parse
    $fields = (function () use ($page) {
      $fields = array_diff($this->fields, $this->excludeFields);

      // If no fields are specified, get all
      if (empty($fields)) {
        $fields = $page->getFields()->explode('name');
      }

      return ['id', 'name', ...$fields];
    })();

    // Parse page data
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

    // Run afterParsePage hook
    if (is_callable($this->afterParsePageHandler)) {
      $hookRet = new HookReturnAfterPageParse();
      $hookRet->parsedPage = $parsedPage;
      $hookRet->page = $page;

      call_user_func($this->afterParsePageHandler, $hookRet);
      $parsedPage = $hookRet->parsedPage;
    }

    return $parsedPage;
  }

  /**
   * Parse multiple pages
   */
  protected function parsePageArray(PageArray $input): array
  {
    return array_map(function (Page $page) {
      return $this->parsePage($page);
    }, $input->getArray());
  }

  /**
   * Parse field
   */
  protected function parseField(string $fieldName, Page $page): mixed
  {
    // Get field object and try to parse it
    $field = $page->getField($fieldName);
    if (!empty($field)) {
      $fieldClassName = (new \ReflectionClass($field->type))->getShortName();

      switch ($fieldClassName) {
        case 'InputfieldCheckbox':
          return (bool) $page->{$field->name};
          break;

        case 'FieldtypeFloat':
          return (float) $page->{$field->name};
          break;

        case 'FieldtypeInteger':
          return (int) $page->{$field->name};
          break;

        default:
          return $page->{$field->name};
          break;
      }
    }

    // If field name is a property, return value as-is
    if ($page->has($fieldName)) {
      return $page->{$fieldName};
    }

    return null;
  }

  /**
   * Parse data and return as array
   */
  public function toArray(): array
  {
    if ($this->input instanceof Page) {
      return $this->parsePage($this->input);
    }

    return $this->parsePageArray($this->input);
  }

  /**
   * Parse data and return as a new Response
   */
  public function toResponse(): Response
  {
    return new Response($this->toArray());
  }
}
