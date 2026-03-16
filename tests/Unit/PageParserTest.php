<?php

use PwJsonApi\{PageParser, PaginatedResponse, Response};
use ProcessWire\{NullPage, PageArray, Pageimage};

test('toArray()', function () {
  $result = (new PageParser())->input(getPage())->toArray();
  expect($result)->toBeArray();

  $result = (new PageParser())->input(new NullPage())->toArray();
  expect($result)->toBeArray();
});

test('toArray() with PageArray input', function () {
  $result = (new PageParser())->input(getMultiplePages())->toArray();
  expect($result)->toBeArray()->toHaveCount(2);
  expect($result[0])->toHaveKey('id');
  expect($result[1])->toHaveKey('id');
});

test('toArray() with empty input', function () {
  $result = (new PageParser())->toArray();
  expect($result)->toBeArray();
});

test('toResponse()', function () {
  $result = (new PageParser())->input(getPage())->toResponse();
  expect($result)->toBeInstanceOf(Response::class);
});

test('input(), getInput(), clearInput()', function () {
  $parser = new PageParser();
  expect($parser->getInput())->toBeNull();

  $page = getPage();
  $parser->input($page);
  expect($parser->getInput())->toBe($page);

  $parser->clearInput();
  expect($parser->getInput())->toBeInstanceOf(PageArray::class);
});

test('string', function () {
  $result = (new PageParser())->input(getPage())->toArray();
  expect($result['text'])->toBeString()->not()->toBeEmpty();

  $result = (new PageParser())->input(getEmptyPage())->toArray();
  expect($result['text'])->toBeString()->toBeEmpty();
});

test('checkbox', function () {
  $result = (new PageParser())->input(getPage())->toArray();
  expect($result['checkbox'])->toBe(true);

  $result = (new PageParser())->input(getEmptyPage())->toArray();
  expect($result['checkbox'])->toBe(false);
});

test('float', function () {
  $result = (new PageParser())->input(getPage())->toArray();
  expect($result['float'])->toBeFloat();

  $result = (new PageParser())->input(getEmptyPage())->toArray();
  expect($result['float'])->toBeNull();
});

test('decimal', function () {
  $result = (new PageParser())->input(getPage())->toArray();
  expect($result['decimal'])->toBeFloat();

  $result = (new PageParser())->input(getEmptyPage())->toArray();
  expect($result['decimal'])->toBeNull();
});

test('integer', function () {
  $result = (new PageParser())->input(getPage())->toArray();
  expect($result['integer'])->toBeInt();

  $result = (new PageParser())->input(getEmptyPage())->toArray();
  expect($result['integer'])->toBeNull();
});

test('Pagefile', function () {
  $result = (new PageParser())->input(getPage())->toArray();
  expect($result['single_file'])->toBeArray();
  expect($result['single_file'])->toHaveKeys([
    'basename',
    'ext',
    'url',
    'filesize',
    'filesize_str',
    'description',
    'tags',
    'created',
    'modified',
  ]);
  expect($result['single_file']['basename'])->toBeString()->not()->toBeEmpty();
  expect($result['single_file']['ext'])->toBeString()->not()->toBeEmpty();
  expect($result['single_file']['url'])->toBeString()->not()->toBeEmpty();
  expect($result['single_file']['filesize'])->toBeInt();
  expect($result['single_file']['filesize_str'])->toBeString();
  expect($result['single_file']['tags'])->toBeArray();
  expect($result['single_file']['created'])->toBeInt();
  expect($result['single_file']['modified'])->toBeInt();

  $result = (new PageParser())->input(getEmptyPage())->toArray();
  expect($result['single_file'])->toBeNull();
});

test('Pagefiles', function () {
  $result = (new PageParser())->input(getPage())->toArray();
  expect($result['multiple_files'])->toBeArray();
  expect($result['multiple_files'][0]['url'])
    ->not()
    ->toBeEmpty();

  $result = (new PageParser())->input(getEmptyPage())->toArray();
  expect($result['multiple_files'])->toBeArray()->toBeEmpty();
});

test('Pageimage', function () {
  $result = (new PageParser())->input(getPage())->toArray();
  expect($result['single_image'])->toBeArray();
  expect($result['single_image'])->toHaveKeys([
    'basename',
    'ext',
    'url',
    'filesize',
    'filesize_str',
    'description',
    'tags',
    'created',
    'modified',
    'width',
    'height',
    '_focus',
    '_ratio',
  ]);
  expect($result['single_image']['width'])->toBeInt();
  expect($result['single_image']['height'])->toBeInt();
  expect($result['single_image']['_focus'])->toBeArray();
  expect($result['single_image']['_ratio'])->toBeFloat();

  $result = (new PageParser())->input(getEmptyPage())->toArray();
  expect($result['single_image'])->toBeNull();
});

test('Pageimages', function () {
  $result = (new PageParser())->input(getPage())->toArray();
  expect($result['multiple_images'])->toBeArray();
  expect($result['multiple_images'][0]['url'])
    ->not()
    ->toBeEmpty();

  $result = (new PageParser())->input(getEmptyPage())->toArray();
  expect($result['multiple_images'])->toBeArray()->toBeEmpty();
});

test('Page', function () {
  $result = (new PageParser())->input(getPage())->toArray();
  expect($result['single_page'])->toBeArray();
  expect($result['single_page']['id'])->toBeInt();

  $result = (new PageParser())->input(getEmptyPage())->toArray();
  expect($result['single_page'])->toBeNull();
});

test('PageArray', function () {
  $result = (new PageParser())->input(getPage())->toArray();
  expect($result['multiple_pages'])->toBeArray();
  expect($result['multiple_pages'][0]['id'])->toBeInt();

  $result = (new PageParser())->input(getEmptyPage())->toArray();
  expect($result['multiple_pages'])->toBeArray()->toBeEmpty();
});

test('SelectableOption', function () {
  $result = (new PageParser())->input(getPage())->toArray();
  expect($result['single_option'])->toBeArray();
  expect($result['single_option']['id'])->toBeInt();
  expect($result['single_option']['value'])->toBeString();
  expect($result['single_option']['title'])->toBeString();

  $result = (new PageParser())->input(getEmptyPage())->toArray();
  expect($result['single_option'])->toBeNull();
});

test('SelectableOptionArray', function () {
  $result = (new PageParser())->input(getPage())->toArray();
  expect($result['multiple_options'])->toBeArray();
  expect($result['multiple_options'][0]['id'])->toBeInt();
  expect($result['multiple_options'][0]['value'])->toBeString();
  expect($result['multiple_options'][0]['title'])->toBeString();

  $result = (new PageParser())->input(getEmptyPage())->toArray();
  expect($result['multiple_options'])->toBeArray()->toBeEmpty();
});

test('properties()', function () {
  $result = (new PageParser())->input(getPage())->toArray();
  expect(array_keys($result))->toContain('id');
  expect(array_keys($result))->toContain('name');

  $result = (new PageParser())
    ->input(getPage())
    ->properties('template', 'numChildren', 'bogus')
    ->toArray();
  expect(array_keys($result))->toContain('id');
  expect(array_keys($result))->toContain('name');
  expect(array_keys($result))->toContain('template');
  expect(array_keys($result))->toContain('numChildren');
  expect(array_keys($result))->not()->toContain('bogus');
});

test('properties() data type handling', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->properties(
      'template',
      'parent',
      'rootParent',
      'numChildren',
      'hasChildren',
      'numParents',
    )
    ->toArray();

  expect($result['template'])->toBeString();
  expect($result['parent']['id'])->toBeInt();
  expect($result['rootParent']['id'])->toBeInt();
  expect($result['numChildren'])->toBeInt();
  expect($result['hasChildren'])->toBeInt();
  expect($result['numParents'])->toBeInt();
});

test('properties() ignores fields', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->properties('checkbox')
    ->fields('title')
    ->toArray();

  expect(array_keys($result))->toContain('id');
  expect(array_keys($result))->toContain('name');
  expect(array_keys($result))->not()->toContain('checkbox');
});

test('excludeProperties()', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->excludeProperties('id')
    ->toArray();

  expect(array_keys($result))->toContain('name');
  expect(array_keys($result))->not()->toContain('id');
});

test('properties() and excludeProperties() combined', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->properties('numChildren', 'hasChildren')
    ->excludeProperties('hasChildren')
    ->toArray();

  expect(array_keys($result))->toContain('id');
  expect(array_keys($result))->toContain('name');
  expect(array_keys($result))->toContain('numChildren');
  expect(array_keys($result))->not()->toContain('hasChildren');
});

test('fields()', function () {
  $result = (new PageParser())->input(getPage())->fields('title')->toArray();
  expect(array_keys($result))->toBe(['id', 'name', 'title']);
});

test('excludeFields()', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->excludeFields('title')
    ->toArray();

  expect(array_keys($result))
    ->not()
    ->toContain(['title']);
});

test('fields() and excludeFields() combined', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->fields('title', 'checkbox')
    ->excludeFields('checkbox')
    ->toArray();

  expect(array_keys($result))->toBe(['id', 'name', 'title']);
});

test('PageParserConfig::parseChildren', function () {
  $result = (new PageParser())
    ->configure(function ($config) {
      $config->parseChildren = true;
    })
    ->input(getPage())
    ->toArray();

  expect($result)->toHaveKey('_children');
  expect($result['_children'])->toBeArray();

  $result = (new PageParser())
    ->configure(function ($config) {
      $config->parseChildren = false;
    })
    ->input(getPage())
    ->toArray();

  expect($result)->not()->toHaveKey('_children');
});

test('PageParserConfig::childrenSelector', function () {
  $result = (new PageParser())
    ->configure(function ($config) {
      $config->parseChildren = true;
      $config->childrenSelector = 'limit=1';
    })
    ->input(getPage())
    ->toArray();

  expect($result)->toHaveKey('_children');
  expect($result['_children'])->toBeArray()->toHaveCount(1);
});

test('PageParserConfig::childrenKey', function () {
  $result = (new PageParser())
    ->configure(function ($config) {
      $config->parseChildren = true;
      $config->childrenKey = '_overwritten_children_key';
    })
    ->input(getPage())
    ->toArray();

  expect($result)->toHaveKey('_overwritten_children_key');
  expect($result['_overwritten_children_key'])->toBeArray();
});

test('PageParserConfig::parsePageReferenceChildren', function () {
  $result = (new PageParser())
    ->configure(function ($config) {
      $config->parsePageReferenceChildren = true;
    })
    ->input(getPage())
    ->toArray();

  expect($result['single_page'])->toHaveKey('_children');
  expect($result['single_page']['_children'])->toBeArray();

  $result = (new PageParser())
    ->configure(function ($config) {
      $config->parsePageReferenceChildren = false;
    })
    ->input(getPage())
    ->toArray();

  expect($result['single_page'])->not()->toHaveKey('_children');
});

test('PageParserConfig::maxDepth', function () {
  $result = (new PageParser())
    ->configure(function ($config) {
      $config->parseChildren = true;
      $config->maxDepth = 1;
    })
    ->input(getPageWithChildren())
    ->toArray();

  $keys = [...getPageStackKeys([$result], 'name')];
  expect($keys)->toHaveLength(1);

  $result = (new PageParser())
    ->configure(function ($config) {
      $config->parseChildren = true;
      $config->maxDepth = 5;
    })
    ->input(getPageWithChildren())
    ->toArray();

  $keys = [...getPageStackKeys([$result], 'name')];
  expect($keys)->toHaveLength(5);
});

test('PageParserConfig::fullFileUrls', function () {
  $result = (new PageParser())
    ->configure(function ($config) {
      $config->fullFileUrls = true;
    })
    ->input(getPage())
    ->toArray();

  expect($result['single_file']['url'])->toStartWith('http://');

  $result = (new PageParser())
    ->configure(function ($config) {
      $config->fullFileUrls = false;
    })
    ->input(getPage())
    ->toArray();

  expect($result['single_file']['url'])->toStartWith('/');
});

test('PageParserConfig::parseFileCustomFields', function () {
  $result = (new PageParser())
    ->configure(function ($config) {
      $config->parseFileCustomFields = true;
    })
    ->input(getPage())
    ->toArray();
  expect($result['single_file'])->toHaveKey('_custom_fields');
  expect($result['single_file']['_custom_fields'])->toBeArray();

  $result = (new PageParser())
    ->configure(function ($config) {
      $config->parseFileCustomFields = false;
    })
    ->input(getPage())
    ->toArray();

  expect($result['single_file'])->not()->toHaveKey('_custom_fields');
});

test('PageParserConfig::fileCustomFieldsKey', function () {
  $result = (new PageParser())
    ->configure(function ($config) {
      $config->parseFileCustomFields = true;
      $config->fileCustomFieldsKey = '_overwritten_custom_fields_key';
    })
    ->input(getPage())
    ->toArray();

  expect($result['single_file'])->toHaveKey('_overwritten_custom_fields_key');
  expect($result['single_file']['_overwritten_custom_fields_key'])->toBeArray();
});

test('hookBeforePageParse()', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->hookBeforePageParse(function ($args) {
      $args->page->title = 'bogus';
    })
    ->toArray();

  expect($result['title'])->toBe('bogus');
});

test('hooks receive correct depth', function () {
  $depths = [];

  (new PageParser())
    ->configure(function ($config) {
      $config->parseChildren = true;
      $config->maxDepth = 3;
    })
    ->input(getPageWithChildren())
    ->fields('title')
    ->hookBeforePageParse(function ($args) use (&$depths) {
      $depths[] = $args->depth;
    })
    ->toArray();

  expect($depths)->toBe([1, 2, 3]);
});

test('hookBeforePageParse() does not mutate original input', function () {
  $page = getPage();
  $originalTitle = $page->title;

  (new PageParser())
    ->input($page)
    ->hookBeforePageParse(function ($args) {
      $args->page->title = 'mutated';
    })
    ->toArray();

  expect($page->title)->toBe($originalTitle);
});

test('hookBeforeFieldParse() can overwrite image field', function () {
  $sourcePage = getPage();

  $result = (new PageParser())
    ->input(getEmptyPage())
    ->hookBeforeFieldParse(function ($args) use ($sourcePage) {
      if ($args->field->name === 'single_image' && empty($args->value)) {
        $args->value = $sourcePage->single_image;
      }
    })
    ->toArray();

  expect($result['single_image'])->toBeArray();
  expect($result['single_image']['url'])->not()->toBeEmpty();
});

test('hookBeforeImageParse() can overwrite image', function () {
  $sourcePage = getPage();
  $sourceImage = $sourcePage->multiple_images->first();

  $result = (new PageParser())
    ->input(getPage())
    ->hookBeforeImageParse(function ($args) use ($sourceImage) {
      if ($args->field->name === 'single_image') {
        $args->image = $sourceImage;
      }
    })
    ->toArray();

  expect($result['single_image'])->toBeArray();
  expect($result['single_image']['basename'])->toBe($sourceImage->basename);
});

test('hookBeforeFileParse() can overwrite file', function () {
  $sourcePage = getPage();
  $sourceFile = $sourcePage->multiple_files->first();

  $result = (new PageParser())
    ->input(getPage())
    ->hookBeforeFileParse(function ($args) use ($sourceFile) {
      if ($args->field?->name === 'single_file') {
        $args->file = $sourceFile;
      }
    })
    ->toArray();

  expect($result['single_file'])->toBeArray();
  expect($result['single_file']['basename'])->toBe($sourceFile->basename);
});

test('hookAfterPageParse()', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->hookAfterPageParse(function ($args) {
      $args->parsedPage['title'] = 'bogus';
    })
    ->toArray();

  expect($result['title'])->toBe('bogus');
});

test('hookBeforePropertyParse()', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->hookBeforePropertyParse(function ($args) {
      if ($args->propertyName === 'id') {
        $args->value = 99999;
      }
    })
    ->toArray();

  expect($result['id'])->toBe(99999);
});

test('hookAfterPropertyParse()', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->hookAfterPropertyParse(function ($args) {
      if ($args->propertyName === 'id') {
        $args->parsedValue = 99999;
      }
    })
    ->toArray();

  expect($result['id'])->toBe(99999);
});

test('hookBeforeFieldParse()', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->hookBeforeFieldParse(function ($args) {
      if ($args->field->name === 'single_page') {
        $args->parser->properties('template');
        $args->parser->fields('title');
      }
    })
    ->toArray();

  expect($result['single_page'])->toBeArray();
  expect(array_keys($result['single_page']))->toBe([
    'id',
    'name',
    'template',
    'title',
  ]);
});

test('hookAfterFieldParse()', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->hookAfterFieldParse(function ($args) {
      if ($args->field->name === 'title') {
        $args->parsedValue = 'bogus';
      }
    })
    ->toArray();

  expect($result['title'])->toBe('bogus');
});

test('hookBeforeImageParse()', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->hookBeforeImageParse(function ($args) {
      if ($args->field->name === 'single_image') {
        $args->image = $args->image->size(100, 100);
      }
    })
    ->toArray();

  expect($result['single_image']['width'])->toBe(100);
  expect($result['single_image']['height'])->toBe(100);
});

test('hookAfterImageParse()', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->hookAfterImageParse(function ($args) {
      if ($args->field->name === 'single_image') {
        $args->parsedImage['_foo'] = 'foo';
      }
    })
    ->toArray();

  expect($result['single_image'])->toHaveKey('_foo');
  expect($result['single_image']['_foo'])->toBe('foo');
});

test('hookBeforeFileParse()', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->hookBeforeFileParse(function ($args) {
      if ($args->field?->name === 'single_file') {
        $args->file->description = 'modified';
      }
    })
    ->toArray();

  expect($result['single_file']['description'])->toBe('modified');
});

test('hookAfterFileParse()', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->hookAfterFileParse(function ($args) {
      if ($args->field->name === 'single_file') {
        $args->parsedFile['_foo'] = 'foo';
      }
    })
    ->toArray();

  expect($result['single_file'])->toHaveKey('_foo');
  expect($result['single_file']['_foo'])->toBe('foo');
});

test('skip() in hookBeforeFieldParse()', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->hookBeforeFieldParse(function ($args) {
      if ($args->field->name === 'title') {
        $args->skip();
      }
    })
    ->toArray();

  expect($result)->not()->toHaveKey('title');
});

test('skip() in hookBeforePropertyParse()', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->hookBeforePropertyParse(function ($args) {
      if ($args->propertyName === 'id') {
        $args->skip();
      }
    })
    ->toArray();

  expect($result)->not()->toHaveKey('id');
  expect($result)->toHaveKey('name');
});

test('skip() in hookBeforePageParse() with PageArray', function () {
  $pages = getMultiplePages();
  $firstId = $pages->first()->id;

  $result = (new PageParser())
    ->input($pages)
    ->hookBeforePageParse(function ($args) use ($firstId) {
      if ($args->page->id === $firstId) {
        $args->skip();
      }
    })
    ->toArray();

  expect($result)->toHaveCount(1);
  expect($result[0]['id'])->not()->toBe($firstId);
});

test('skip() in hookBeforePageParse() with single Page', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->hookBeforePageParse(function ($args) {
      $args->skip();
    })
    ->toArray();

  expect($result)->toBe([]);
});

test('skip() short-circuits remaining hooks', function () {
  $secondHookCalled = false;

  $result = (new PageParser())
    ->input(getPage())
    ->hookBeforeFieldParse(function ($args) {
      if ($args->field->name === 'title') {
        $args->skip();
      }
    })
    ->hookBeforeFieldParse(function ($args) use (&$secondHookCalled) {
      if ($args->field->name === 'title') {
        $secondHookCalled = true;
      }
    })
    ->toArray();

  expect($secondHookCalled)->toBeFalse();
  expect($result)->not()->toHaveKey('title');
});

test('toPaginatedResponse() with PageArray', function () {
  $result = (new PageParser())
    ->input(getMultiplePages())
    ->toPaginatedResponse();

  expect($result)->toBeInstanceOf(PaginatedResponse::class);

  $array = $result->toArray();
  expect($array)->toHaveKey('data');
  expect($array)->toHaveKey('pagination');
  expect($array['pagination']['start'])->toBeInt();
  expect($array['pagination']['limit'])->toBeInt();
  expect($array['pagination']['total'])->toBeInt();
  expect($array['pagination']['page'])->toBeInt();
  expect($array['pagination']['pages'])->toBeInt();
});

test('toPaginatedResponse() throws with single Page', function () {
  (new PageParser())->input(getPage())->toPaginatedResponse();
})->throws(
  InvalidArgumentException::class,
  'toPaginatedResponse() requires a PageArray input',
);
