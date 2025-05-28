<?php

namespace PwJsonApi;

use \ProcessWire\{PageArray, Page, PageFile, PageFiles, PageImage, PageImages};
use function ProcessWire\wire;

// TODO: global configuration for page parser
class PageParser
{
  /**
   * Fields names to parse
   *
   * @var string[]
   */
  protected array $fields = ['id', 'name'];

  /**
   * Fields names to exclude
   *
   * @var string[]
   */
  protected array $excludeFields = [];

  /**
   * Page or pages to parse
   */
  protected Page|PageArray|null $input = null;

  /**
   * Handlers for before parse page
   *
   * @var callable[]
   */
  protected $beforeParsePageHandlers = [];

  /**
   * Handlers for after parse page
   *
   * @var callable[]
   */
  protected $afterParsePageHandlers = [];

  /**
   * Handlers for before parse field
   *
   * @var callable[]
   */
  protected $beforeParseFieldHandlers = [];

  /**
   * Handlers for after parse field
   *
   * @var callable[]
   */
  protected $afterParseFieldHandlers = [];

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
   * Add hook to run before a single page will be parsed
   *
   * @param callable(HookReturnBeforePageParse): void $handler
   */
  public function hookBeforePageParse(callable $handler): static
  {
    $this->beforeParsePageHandlers[] = $handler;
    return $this;
  }

  /**
   * Add hook to run after a single page has been parsed
   *
   * @param callable(HookReturnAfterPageParse): void $handler
   */
  public function hookAfterPageParse(callable $handler): static
  {
    $this->afterParsePageHandlers[] = $handler;
    return $this;
  }

  /**
   * Add hook to run before a field will be parsed
   *
   * @param callable(HookReturnBeforeFieldParse): void $handler
   */
  public function hookBeforeFieldParse(callable $handler): static
  {
    $this->beforeParseFieldHandlers[] = $handler;
    return $this;
  }

  /**
   * Add hook to run after a field has been parsed
   *
   * @param callable(HookReturnAfterFieldParse): void $handler
   */
  public function hookAfterFieldParse(callable $handler): static
  {
    $this->afterParseFieldHandlers[] = $handler;
    return $this;
  }

  /**
   * Parse a single page
   */
  protected function parsePage(\ProcessWire\Page $page): array
  {
    if (!empty($this->beforeParsePageHandlers)) {
      // Run beforeParsePage hook
      $hookRet = new HookReturnBeforePageParse();

      foreach ($this->beforeParsePageHandlers as $handler) {
        if (is_callable($handler)) {
          $hookRet->page = $page;
          call_user_func($handler, $hookRet);
          $page = $hookRet->page;
        }
      }
    }

    // Gather fields to parse
    $fields = (function () use ($page) {
      $fields = array_diff($this->fields, $this->excludeFields);

      // If no fields are specified, get all
      if (empty($fields)) {
        $fields = $page->getFields()->explode('name');
      }

      return $fields;
    })();

    // Parse page data
    $parsedPage = array_reduce(
      $fields,
      function ($acc, $fieldName) use ($page) {
        $acc[$fieldName] = $this->parseField($fieldName, $page);
        return $acc;
      },
      []
    );

    // Run afterParsePage hook
    if (!empty($this->afterParsePageHandlers)) {
      $hookRet = new HookReturnAfterPageParse();
      $hookRet->page = $page;

      foreach ($this->afterParsePageHandlers as $handler) {
        if (is_callable($handler)) {
          $hookRet->parsedPage = $parsedPage;
          call_user_func($handler, $hookRet);
          $parsedPage = $hookRet->parsedPage;
        }
      }
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
  // TODO: pass parse handlers to sub-parsers
  protected function parseField(string $fieldName, Page $page): mixed
  {
    $field = $page->getField($fieldName);
    $value = $page->{$fieldName};
    $parser = new PageParser();

    if (!empty($field)) {
      if (!empty($this->beforeParseFieldHandlers)) {
        // Run before parse field hooks
        $hookRet = new HookReturnBeforeFieldParse();
        $hookRet->field = $field;
        $hookRet->page = $page;

        foreach ($this->beforeParseFieldHandlers as $handler) {
          if (is_callable($handler)) {
            $hookRet->value = $value;
            $hookRet->parser = $parser;

            call_user_func($handler, $hookRet);

            // Update value and parser from callback
            $value = $hookRet->value;
            $parser = $hookRet->parser;
          }
        }
      }

      $parsedValue = (function () use ($value, $field, $parser) {
        $fieldClassName = (new \ReflectionClass($field->type))->getShortName();

        if ($fieldClassName === 'FieldtypeCheckbox') {
          return (bool) $value;
        } elseif ($fieldClassName === 'FieldtypeFloat') {
          return (float) $value;
        } elseif ($fieldClassName === 'FieldtypeInteger') {
          return (int) $value;
        } elseif ($value instanceof Pageimage) {
          return $this->parseImage($value);
        } elseif ($value instanceof Pageimages) {
          return array_reduce(
            $value->getArray(),
            function ($acc, $image) {
              $acc[] = $this->parseImage($image);
              return $acc;
            },
            []
          );
        } elseif ($value instanceof PageFile) {
          return $this->parseFile($value);
        } elseif ($value instanceof PageFiles) {
          return array_reduce(
            $value->getArray(),
            function ($acc, $file) {
              $acc[] = $this->parseFile($file);
              return $acc;
            },
            []
          );
        } elseif ($value instanceof Page) {
          if ($value === false || !$value->id) {
            return null;
          }

          return $parser->parse($value)->toArray();
        } elseif ($value instanceof PageArray) {
          return $parser->parse($value)->toArray();
        } else {
          return $value;
        }
      })();

      // Run after parse field hooks
      if (!empty($this->afterParseFieldHandlers)) {
        $hookRet = new HookReturnAfterFieldParse();
        $hookRet->field = $field;
        $hookRet->page = $page;

        foreach ($this->afterParseFieldHandlers as $handler) {
          if (is_callable($handler)) {
            $hookRet->value = $parsedValue;

            call_user_func($handler, $hookRet);
            $parsedValue = $hookRet->value;
          }
        }
      }

      return $parsedValue;
    }

    // If field name is a property, return value as-is
    if ($page->has($fieldName)) {
      return $page->{$fieldName};
    }

    return null;
  }

  // TODO: hooks for this?
  public function parseFile(PageFile $file)
  {
    // Parser for custom fields
    $parser = new PageParser();

    $out = [
      'url' => $file->httpUrl,
      'filesize' => $file->filesize,
      'filesize_str' => $file->filesizeStr,
      'description' => !empty($file->description) ? $file->description : null,
      'tags' => !empty($file->tags) ? $file->tags : null,
      'created' => $file->created,
      'modified' => $file->modified,
    ];

    /** @var \ProcessWire\Template|null */
    $customFieldsTpl = $file->field->getFieldtype()->getFieldsTemplate($file->field);
    if ($customFieldsTpl && $customFieldsTpl->fields->count()) {
      // Create temporary page for custom fields
      // This page will be used to feed the parser.
      // This will cause some overhead, but it's acceptable for now.
      $tempCustomFieldsPage = new Page();
      $tempCustomFieldsPage->template = $customFieldsTpl;

      foreach ($customFieldsTpl->fields as $customField) {
        $tempCustomFieldsPage->set($customField->name, $file->get($customField->name));
      }

      $out['_custom_fields'] = $parser
        ->excludeFields('id', 'name')
        ->parse($tempCustomFieldsPage)
        ->toArray();
    }

    return $out;
  }

  // TODO: hooks for this?
  public function parseImage(PageImage $image)
  {
    $out = [
      ...$this->parseFile($image),
      'width' => $image->width,
      'height' => $image->height,
      'focus' => $image->focus(),
      '_aspect_ratio' => $image->ratio(),
    ];

    return $out;
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
