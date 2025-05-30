<?php

namespace PwJsonApi;

use \ProcessWire\{
  PageArray,
  Page,
  Field,
  PageFile,
  PageFiles,
  PageImage,
  PageImages
};

// TODO: global configuration for page parser
class PageParser
{
  use HasPageParserHooks;

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
   * Properties to parse
   *
   * @var string[]
   */
  protected array $properties = ['id', 'name'];

  /**
   * Page or pages to parse
   */
  protected Page|PageArray|null $input = null;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->hooks = new PageParserHooks();
  }

  /**
   * Set Page of PageArray to parse
   *
   * This method can be ran multiple times, and the new payload
   * will be merged with the previous one.
   */
  public function input(PageArray|Page $input): static
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
    $this->excludeFields = array_unique([
      ...$this->excludeFields,
      ...$excludeFields,
    ]);
    return $this;
  }

  /**
   * Parse a single page
   */
  protected function parsePage(\ProcessWire\Page $page): array
  {
    // Run BeforePageParse hooks
    $beforePageParseHooks = $this->getPageParserHook(
      PageParserHookKey::BeforePageParse
    );
    if (!empty($beforePageParseHooks)) {
      $hookRet = new HookReturnBeforePageParse();

      foreach ($beforePageParseHooks as $handler) {
        if (is_callable($handler)) {
          $hookRet->page = $page;
          call_user_func($handler, $hookRet);
          $page = $hookRet->page;
        }
      }
    }

    // Gather fields to parse
    $fields = (function () use ($page) {
      $fields = !empty($this->fields)
        ? $this->fields
        : $page->getFields()->explode('name');

      // // If no fields are specified, get all
      // if (empty($fields)) {
      //   $fields = $page->getFields()->explode('name');
      // }

      return array_diff(
        [...$this->properties, ...$fields],
        $this->excludeFields
      );
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

    // Run AfterPageParse hooks
    $afterPageParseHooks = $this->getPageParserHook(
      PageParserHookKey::AfterPageParse
    );
    if (!empty($afterPageParseHooks)) {
      $hookRet = new HookReturnAfterPageParse();
      $hookRet->page = $page;

      foreach ($afterPageParseHooks as $handler) {
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
      // Run BeforeFieldParse hooks
      $beforeFieldParseHooks = $this->getPageParserHook(
        PageParserHookKey::BeforeFieldParse
      );
      if (!empty($beforeFieldParseHooks)) {
        $hookRet = new HookReturnBeforeFieldParse();
        $hookRet->field = $field;
        $hookRet->page = $page;

        foreach ($beforeFieldParseHooks as $handler) {
          if (is_callable($handler)) {
            $hookRet->value = $value;
            $hookRet->parser = $parser;

            call_user_func($handler, $hookRet);

            $value = $hookRet->value;
            $parser = $hookRet->parser;
          }
        }
      }

      $parsedValue = (function () use ($value, $field, $page, $parser) {
        $fieldClassName = (new \ReflectionClass($field->type))->getShortName();

        if ($fieldClassName === 'FieldtypeCheckbox') {
          return (bool) $value;
        } elseif ($fieldClassName === 'FieldtypeFloat') {
          return (float) $value;
        } elseif ($fieldClassName === 'FieldtypeInteger') {
          return (int) $value;
        } elseif ($value instanceof Pageimage) {
          return $this->parseImage($value, $field, $page);
        } elseif ($value instanceof Pageimages) {
          return array_reduce(
            $value->getArray(),
            function ($acc, $image) use ($field, $page) {
              $acc[] = $this->parseImage($image, $field, $page);
              return $acc;
            },
            []
          );
        } elseif ($value instanceof PageFile) {
          return $this->parseFile($value, $field, $page);
        } elseif ($value instanceof PageFiles) {
          return array_reduce(
            $value->getArray(),
            function ($acc, $file) use ($field, $page) {
              $acc[] = $this->parseFile($file, $field, $page);
              return $acc;
            },
            []
          );
        } elseif ($value instanceof Page) {
          if ($value === false || !$value->id) {
            return null;
          }

          return $parser->input($value)->toArray();
        } elseif ($value instanceof PageArray) {
          return $parser->input($value)->toArray();
        } else {
          return $value;
        }
      })();

      // Run AfterFieldParse hooks
      $afterFieldParseHooks = $this->getPageParserHook(
        PageParserHookKey::AfterFieldParse
      );
      if (!empty($afterFieldParseHooks)) {
        $hookRet = new HookReturnAfterFieldParse();
        $hookRet->field = $field;
        $hookRet->page = $page;

        foreach ($afterFieldParseHooks as $handler) {
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

  /**
   * Parse PageFile object
   */
  public function parseFile(
    PageFile $file,
    ?Field $field = null,
    ?Page $page = null,
    ?PageParser $parser = null
  ) {
    // Parser for custom fields of the file,
    $parser = $parser ?? new PageParser();
    $parser->excludeFields('id', 'name');

    // Run BeforeFileParse hooks
    $beforeFileParseHooks = $this->getPageParserHook(
      PageParserHookKey::BeforeFileParse
    );
    if (!empty($beforeFileParseHooks)) {
      $hookRet = new HookReturnBeforeFileParse();
      $hookRet->field = $field;
      $hookRet->page = $page;

      foreach ($beforeFileParseHooks as $handler) {
        if (is_callable($handler)) {
          $hookRet->file = $file;
          $hookRet->parser = $parser;
          call_user_func($handler, $hookRet);
          $file = $hookRet->file;
          $parser = $hookRet->parser;
        }
      }
    }

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
    $customFieldsTpl = $file->field
      ->getFieldtype()
      ->getFieldsTemplate($file->field);
    if ($customFieldsTpl?->fields->count()) {
      // Create temporary page for custom fields
      // This page will be used to feed the parser.
      // This will introduce some overhead, but it's acceptable for now.
      $tempCustomFieldsPage = new Page();
      $tempCustomFieldsPage->template = $customFieldsTpl;

      foreach ($customFieldsTpl->fields as $customField) {
        $tempCustomFieldsPage->set(
          $customField->name,
          $file->get($customField->name)
        );
      }

      $out['_custom_fields'] = $parser->input($tempCustomFieldsPage)->toArray();
    }

    // Run AfterFileParse hooks
    $afterFileParseHooks = $this->getPageParserHook(
      PageParserHookKey::AfterFileParse
    );
    if (!empty($afterFileParseHooks)) {
      $hookRet = new HookReturnAfterFileParse();
      $hookRet->file = $file;
      $hookRet->field = $field;
      $hookRet->page = $page;

      foreach ($afterFileParseHooks as $handler) {
        if (is_callable($handler)) {
          $hookRet->parsedFile = $out;
          call_user_func($handler, $hookRet);
          $out = $hookRet->parsedFile;
        }
      }
    }

    return $out;
  }

  /**
   * Parse PageImage object
   */
  public function parseImage(
    PageImage $image,
    ?Field $field = null,
    ?Page $page = null,
    ?PageParser $parser = null
  ) {
    // Parser for custom fields of the file,
    $parser = $parser ?? new PageParser();
    $parser->excludeFields('id', 'name');

    // Run BeforeImageParse hooks
    $beforeImageParseHooks = $this->getPageParserHook(
      PageParserHookKey::BeforeImageParse
    );
    if (!empty($beforeImageParseHooks)) {
      $hookRet = new HookReturnBeforeImageParse();
      $hookRet->field = $field;
      $hookRet->page = $page;

      foreach ($beforeImageParseHooks as $handler) {
        if (is_callable($handler)) {
          $hookRet->image = $image;
          $hookRet->parser = $parser;
          call_user_func($handler, $hookRet);
          $image = $hookRet->image;
          $parser = $hookRet->parser;
        }
      }
    }

    // Parse as file, and include some image-specific keys
    $out = [
      ...$this->parseFile($image, $field, $page, $parser),
      'width' => $image->width,
      'height' => $image->height,
      '_focus' => $image->focus(),
      '_aspect_ratio' => $image->ratio(),
    ];

    // Run AfterImageParse hooks
    $afterImageParseHooks = $this->getPageParserHook(
      PageParserHookKey::AfterImageParse
    );
    if (!empty($afterImageParseHooks)) {
      $hookRet = new HookReturnAfterImageParse();
      $hookRet->image = $image;
      $hookRet->field = $field;
      $hookRet->page = $page;

      foreach ($afterImageParseHooks as $handler) {
        if (is_callable($handler)) {
          $hookRet->parsedImage = $out;
          call_user_func($handler, $hookRet);
          $out = $hookRet->parsedImage;
        }
      }
    }

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
