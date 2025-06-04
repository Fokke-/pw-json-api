<?php

namespace PwJsonApi;

use \ProcessWire\{
  PageArray,
  Page,
  Template,
  Field,
  PageFile,
  PageFiles,
  PageImage,
  PageImages,
  SelectableOptionArray
};

/**
 * ProcessWire Page parser
 */
class PageParser
{
  use HasPageParserHooks;

  /**
   * Configuration
   */
  private PageParserConfig $config;

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
   * Current depth in recursive parsing
   *
   * @internal
   */
  public int $_currentDepth = 1;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->config = new PageParserConfig();
    $this->hooks = new PageParserHooks();
  }

  /**
   * Configure page parser
   *
   * @param callable(PageParserConfig): void $configure Configuration function
   */
  public function configure(callable|null $configure = null): static
  {
    if (is_callable($configure)) {
      call_user_func($configure, $this->config);
    }

    return $this;
  }

  /**
   * Set Page of PageArray to parse
   */
  public function input(PageArray|Page $input): static
  {
    $this->input = $input;
    return $this;
  }

  /**
   * Get current input
   */
  public function getInput(): PageArray|Page
  {
    return $this->input;
  }

  /**
   * Clear input
   *
   * Alias for calling input() with no arguments
   */
  public function clearInput(): static
  {
    $this->input = null;
    return $this;
  }

  /**
   * Specify fields to parse
   */
  public function fields(string ...$fields): static
  {
    $this->fields = array_unique($fields);
    return $this;
  }

  /**
   * Specify fields to exclude
   */
  public function excludeFields(string ...$excludeFields): static
  {
    $this->excludeFields = array_unique($excludeFields);
    return $this;
  }

  /**
   * Parse a single page
   */
  protected function parsePage(\ProcessWire\Page $page): array
  {
    // Use current parser as a base for child page parser
    $childPageParser = clone $this;
    $childPageParser->config = clone $this->config;
    $childPageParser->_currentDepth = $this->_currentDepth + 1;

    // Run BeforePageParse hooks
    $beforePageParseHooks = $this->getPageParserHooks(
      PageParserHookKey::BeforePageParse
    );

    if (!empty($beforePageParseHooks)) {
      $hookRet = new HookReturnBeforePageParse();

      foreach ($beforePageParseHooks as $handler) {
        if (is_callable($handler)) {
          $hookRet->page = $page;
          $hookRet->parser = $childPageParser;
          call_user_func($handler, $hookRet);
          $page = $hookRet->page;
        }
      }
    }

    // Gather fields to parse
    $fields = (function () use ($page) {
      $resolvedFields = !empty($this->fields)
        ? $this->fields
        : $page->getFields()->explode('name');

      return array_diff(
        [...$this->properties, ...$resolvedFields],
        $this->excludeFields
      );
    })();

    // Parse page data
    $parsedPage = array_reduce(
      $fields,
      function ($acc, $fieldName) use ($page) {
        $field = $page->getField($fieldName);

        // Skip non-existant properties
        if (!$page->has($fieldName)) {
          return $acc;
        }

        // Skip certain field types
        if (
          !empty($field) &&
          in_array($field->type, [
            'FieldtypeCache',
            'FieldtypeComments', // TODO: return this
            'FieldtypeFieldsetOpen',
            'FieldtypeFieldsetTabOpen',
            'FieldtypeFieldsetClose',
          ])
        ) {
          return $acc;
        }

        $acc[$fieldName] = $this->parseField($fieldName, $page);
        return $acc;
      },
      []
    );

    // Parse child pages
    if (
      $this->config->parseChildren === true &&
      $page->numChildren($this->config->childrenSelector) &&
      $this->_currentDepth < $this->config->maxDepth
    ) {
      $parsedPage[$this->config->childrenKey] = $childPageParser
        ->input($page->children($this->config->childrenSelector))
        ->toArray();
    }

    // Run AfterPageParse hooks
    $afterPageParseHooks = $this->getPageParserHooks(
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
  protected function parseField(string $fieldName, Page $page): mixed
  {
    $field = $page->getField($fieldName);
    $value = $page->{$fieldName};

    // If field was not found, try to parse as property
    if (empty($field) && $page->has($fieldName)) {
      if ($value instanceof Template) {
        return $value->name;
      }

      return $value;
    }

    if (!empty($field)) {
      // Clone current parser. This will be used for fields with
      // any sort of page reference as a value.
      $parser = clone $this;
      $parser->config = clone $this->config;
      $parser->config->parseChildren =
        $parser->config->parsePageReferenceChildren;

      // Run BeforeFieldParse hooks
      $beforeFieldParseHooks = $this->getPageParserHooks(
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
          return $value === '' ? null : (float) $value;
        } elseif ($fieldClassName === 'FieldtypeDecimal') {
          return $value === '' ? null : (float) $value;
        } elseif ($fieldClassName === 'FieldtypeInteger') {
          return $value === '' ? null : (int) $value;
        } elseif ($fieldClassName === 'FieldtypeToggle') {
          return $value === '' ? null : $value;
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
        } elseif ($value instanceof SelectableOptionArray) {
          // Single option
          if (
            in_array($field->inputfieldClass, [
              'InputfieldRadios',
              'InputfieldSelect',
            ])
          ) {
            $option = $value->first();
            if (!$option) {
              return null;
            }

            return [
              'id' => $option->id,
              'value' => $option->value,
              'title' => $option->title,
            ];
          }

          // Multiple options
          return array_reduce(
            $value->getArray(),
            function ($acc, $option) {
              $acc[] = [
                'id' => $option->id,
                'value' => $option->value,
                'title' => $option->title,
              ];
              return $acc;
            },
            []
          );
        } else {
          return $value;
        }
      })();

      // Run AfterFieldParse hooks
      $afterFieldParseHooks = $this->getPageParserHooks(
        PageParserHookKey::AfterFieldParse
      );
      if (!empty($afterFieldParseHooks)) {
        $hookRet = new HookReturnAfterFieldParse();
        $hookRet->field = $field;
        $hookRet->page = $page;

        foreach ($afterFieldParseHooks as $handler) {
          if (is_callable($handler)) {
            $hookRet->parsedValue = $parsedValue;
            call_user_func($handler, $hookRet);
            $parsedValue = $hookRet->parsedValue;
          }
        }
      }

      return $parsedValue;
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
    $beforeFileParseHooks = $this->getPageParserHooks(
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
      'url' =>
        $this->config->fullFileUrls === true ? $file->httpUrl : $file->url,
      'filesize' => $file->filesize,
      'filesize_str' => $file->filesizeStr,
      'description' => !empty($file->description) ? $file->description : null,
      'tags' => !empty($file->tags) ? $file->tags : null,
      'created' => $file->created,
      'modified' => $file->modified,
    ];

    if ($this->config->parseFileCustomFields === true) {
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

        $tempCustomFieldsPage->of(true);
        $out[$this->config->fileCustomFieldsKey] = $parser
          ->input($tempCustomFieldsPage)
          ->toArray();
      }
    }

    // Run AfterFileParse hooks
    $afterFileParseHooks = $this->getPageParserHooks(
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

    // Run BeforeImageParse hooks
    $beforeImageParseHooks = $this->getPageParserHooks(
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

    // Parse as file, but include some image-specific keys
    $out = [
      ...$this->parseFile($image, $field, $page, $parser),
      'width' => $image->width,
      'height' => $image->height,
      '_focus' => $image->focus(),
      '_ratio' => $image->ratio(),
    ];

    // Run AfterImageParse hooks
    $afterImageParseHooks = $this->getPageParserHooks(
      PageParserHookKey::AfterImageParse
    );
    if (!empty($afterImageParseHooks)) {
      $hookRet = new HookReturnAfterImageParse();
      $hookRet->image = $image;
      $hookRet->originalImage = $image->getOriginal() ?? $image;
      $hookRet->field = $field;
      $hookRet->page = $page;
      $hookRet->parser = $parser;

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
