<?php

use PwJsonApi\{PageParser, Response};
use ProcessWire\{NullPage};

test('toArray()', function () {
  $result = (new PageParser())->input(getPage())->toArray();
  expect($result)->toBeArray();

  $result = (new PageParser())->input(new NullPage())->toArray();
  expect($result)->toBeArray();
});

test('toArray() with empty input', function () {
  $result = (new PageParser())->toArray();
  expect($result)->toBeArray();
});

test('toResponse()', function () {
  $result = (new PageParser())->input(getPage())->toResponse();
  expect($result)->toBeInstanceOf(Response::class);
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
  expect($result['single_file']['url'])->not()->toBeEmpty();

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
  expect($result['single_image']['url'])->not()->toBeEmpty();

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
  expect(array_keys($result))->toContain('template');

  $result = (new PageParser())
    ->input(getPage())
    ->properties('numChildren', 'bogus')
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
  expect(array_keys($result))->toContain('template');
  expect(array_keys($result))->not()->toContain('checkbox');
});

test('excludeProperties()', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->excludeProperties('id', 'template')
    ->toArray();

  expect(array_keys($result))->toContain('name');
  expect(array_keys($result))->not()->toContain('id');
  expect(array_keys($result))->not()->toContain('template');
});

test('properties() and excludeProperties() combined', function () {
  $result = (new PageParser())
    ->input(getPage())
    ->properties('numChildren', 'hasChildren')
    ->excludeProperties('hasChildren')
    ->toArray();

  expect(array_keys($result))->toContain('id');
  expect(array_keys($result))->toContain('name');
  expect(array_keys($result))->toContain('template');
  expect(array_keys($result))->toContain('numChildren');
  expect(array_keys($result))->not()->toContain('hasChildren');
});

test('fields()', function () {
  $result = (new PageParser())->input(getPage())->fields('title')->toArray();
  expect(array_keys($result))->toBe(['id', 'name', 'template', 'title']);
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
    ->fields('title')
    ->excludeFields('title')
    ->toArray();

  expect(array_keys($result))->toBe(['id', 'name', 'template']);
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
