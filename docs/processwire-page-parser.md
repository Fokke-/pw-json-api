# ProcessWire page parser

The **Page Parser** is a flexible tool for converting ProcessWire pages into structured data, ready for API responses. It supports all ProcessWire core field types and offers extensive configuration and hook options.

- Parse single pages or arrays of pages
- Select specific fields to include or exclude
- Optionally include child pages, with depth and selector controls
- Use hooks to customize parsing at various stages (pages, fields, images, files)

## Basic usage example

::: tip
Note that `toArray()` or `toResponse()` starts the actual parsing. Any configuration must be done before calling one of these methods.
:::

```php
use PwJsonApi\PageParser;

$output = (new PageParser())
  ->input(wire()->pages->find('template=basic-page'))
  ->fields('title', 'body')
  ->toArray();
```

## Configuration

Configure the parser using the `configure()` method, which allows you to control recursion, child page selection, output format, and more.

```php
$parser->configure(function ($config) {
  /** Recursively parse child pages? */
  $config->parseChildren = false;

  /** Recursively parse children of page field references? */
  $config->parsePageFieldChildren = false;

  /** Maximum depth for recursive parsing */
  $config->maxDepth = 3;

  /** Selector for child pages */
  $config->childrenSelector = '';

  /** Key name for child pages */
  $config->childrenKey = '_children';

  /** Output full file URLs */
  $config->fullFileUrls = true;
});
```

## Input

Specify the pages to parse with `input()`. Accepts a `Page` or `PageArray`.

```php
$parser->input(wire()->pages->find('template=basic-page'));
```

## Field Selection

### Include fields

By default, all fields of the input pages will be included. Use `fields()` to include only specific fields.

::: tip
If your input contains pages with different templates, you can still specify all fields here. If the parsed template does not have the given field, it will be ignored.
:::

```php
$parser->fields('title', 'body');
```

### Exclude fields

Use `excludeFields()` to omit certain fields.

```php
$parser->excludeFields('body');
```

## Child pages

Enable child page parsing via [parser configuration](#configuration). Fine-tune with selectors, depth, and [`hooks`](#hookbeforepageparse).

## Output

### As an array

`toArray()` returns the parsed data as an array.

```php{4}
$result = (new PageParser())
  ->input(wire()->pages->find('template=basic-page'))
  ->fields('title', 'body')
  ->toArray();
```

### As a response

`toResponse()` returns a Response object.

```php{4}
$response = (new PageParser())
  ->input(wire()->pages->find('template=basic-page'))
  ->fields('title', 'body')
  ->toResponse();
```

## Hooks

Hooks allow you to modify data before and after parsing pages, fields, images, and files. This enables advanced customization, such as resizing images, altering field values, or adding extra data.

::: tip
Hooks must be defined before the `toArray()` or `toResponse()` call.
:::

### hookBeforePageParse()

Use this to modify the source page.

```php
$parser->hookBeforePageParse(function ($args) {
  // Get only the first three tags
  $args->page->tags = $args->page->tags->slice(0, 3);

  // Do some other nasty modifications to the source page
  $args->page->title = 'Remember that it was your idea...';

  // Reconfigure parser for child pages
  $args->parser->fields('title');
});
```

### hookAfterPageParse()

Use this to modify the final parsed data of the page, add extra keys etc.

```php
$parser->hookAfterPageParse(function ($args) {
  $args->parsedPage['_foo'] = 'foo';

  if ($args->page->template->name === 'bar') {
    $args->parsedPage['_bar'] = 'bar';
  }
});
```

### hookBeforeFieldParse()

Use this to access field value, source field, source page, and another `PageParser` instance, which will be used for fields with `Page` or `PageArray` as a value.

::: tip

At first glance, this hook seems like the right place to resize images. However, since the image field may contain multiple images, resizing images here would force you to re-map the entire `Pageimages` object. Instead, use the dedicated [`hookBeforeImageParse()`](#hookbeforeimageparse) hook.
:::

```php
$parser->hookBeforeFieldParse(function ($args) {
  // Reconfigure parser for the field "tags" of
  // "basic-page" template.
  if (
    $args->page->template->name === 'basic-page' &&
    $args->field->name === 'tags'
  ) {
    $args->parser->fields('title');
  }
});
```

### hookAfterFieldParse()

Use this to modify final parsed output of field.

```php
$parser->hookAfterFieldParse(function ($args) {
  if ($args->field->name === 'title') {
    $args->parsedValue = ucfirst($args->parsedValue);
  }
});
```

### hookBeforeImageParse()

Use this to modify the source image. This hook will run for every image, regardless of whether the field contains multiple images. This is a good place to resize images to avoid serving them at their original size.

`PageParser` instance for custom fields of files is exposed in `$args`.

```php
$parser->hookBeforeImageParse(function ($args) {
  if ($args->field->name === 'my_image_field') {
    // Resize image
    $args->image = $args->image->size(300, 200);

    // Reconfigure parser for custom fields
    $args->parser->fields('title');
  }
});
```

### hookAfterImageParse()

Use this to modify final parsed output of an image.

```php
$parser->hookAfterImageParse(function ($args) {
  if ($args->field->name === 'my_image_field') {
    // Even though the parser in $args is primarily used for
    // custom fields of the image, you can use it to create thumbnails.
    $args->parsedImage['_thumbnails'] = [
      '200x200' => $args->parser->parseImage(
        $args->originalImage->size(200, 200)
      ),
      '100x100' => $args->parser->parseImage(
        $args->originalImage->size(100, 100)
      ),
    ];
  }
});
```

### hookBeforeFileParse()

Use this to modify the source file. This hook will run for every file, regardless of whether the field contains multiple files.

`PageParser` instance for custom fields of files is exposed in `$args`.

```php
$parser->hookBeforeFileParse(function ($args) {
  if ($args->field->name === 'my_file_field') {
    // Reconfigure parser for custom fields
    $args->parser->fields('title');
  }
});
```

### hookAfterFileParse()

Use this to modify final parsed output of a file.

```php
$parser->hookAfterFileParse(function ($args) {
  if ($args->field->name === 'my_file_field') {
    $args->parsedFile['_foo'] = 'bar';
  }
});
```
