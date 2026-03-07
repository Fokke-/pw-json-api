<?php

use PwJsonApi\{Api, PluginList};
use PwJsonApi\Plugins\ApiPlugin;
use ProcessWire\WireException;

function createTestPluginA(): ApiPlugin
{
  return new class extends ApiPlugin {
    public bool $initialized = false;

    public function init(
      PwJsonApi\Api|PwJsonApi\Service|PwJsonApi\Endpoint $context,
    ): static {
      parent::init($context);
      $this->initialized = true;
      return $this;
    }
  };
}

function createTestPluginB(): ApiPlugin
{
  return new class extends ApiPlugin {
    public bool $initialized = false;

    public function init(
      PwJsonApi\Api|PwJsonApi\Service|PwJsonApi\Endpoint $context,
    ): static {
      parent::init($context);
      $this->initialized = true;
      return $this;
    }
  };
}

test('add() stores a plugin', function () {
  $list = new PluginList();
  $plugin = createTestPluginA();

  $list->add($plugin);

  expect($list->getAll())->toHaveCount(1);
});

test('add() throws on duplicate plugin class', function () {
  $list = new PluginList();
  $list->add(createTestPluginA());

  expect(fn() => $list->add(createTestPluginA()))->toThrow(
    WireException::class,
  );
});

test('getAll() returns all plugins', function () {
  $list = new PluginList();
  $list->add(createTestPluginA());
  $list->add(createTestPluginB());

  expect($list->getAll())->toHaveCount(2);
});

test('get() retrieves plugin by class name', function () {
  $list = new PluginList();
  $plugin = createTestPluginA();

  $list->add($plugin);

  expect($list->get($plugin::class))->toBe($plugin);
  expect($list->get(ApiPlugin::class))->toBe($plugin);
});

test('get() returns null for missing plugin', function () {
  $list = new PluginList();

  expect($list->get(ApiPlugin::class))->toBeNull();
});

test('has() returns correct value', function () {
  $list = new PluginList();
  $plugin = createTestPluginA();

  expect($list->has($plugin::class))->toBeFalse();

  $list->add($plugin);

  expect($list->has($plugin::class))->toBeTrue();
  expect($list->has(ApiPlugin::class))->toBeTrue();
});

test('_initAll() calls init() on each plugin', function () {
  $list = new PluginList();
  $pluginA = createTestPluginA();
  $pluginB = createTestPluginB();

  $list->add($pluginA);
  $list->add($pluginB);

  expect($pluginA->initialized)->toBeFalse();
  expect($pluginB->initialized)->toBeFalse();

  $api = new Api();
  $list->_initAll($api);

  expect($pluginA->initialized)->toBeTrue();
  expect($pluginB->initialized)->toBeTrue();
});

test('addPlugin() runs setup callback', function () {
  $api = new Api();
  $plugin = createTestPluginA();
  $setupCalled = false;

  $api->addPlugin($plugin, function ($p) use (&$setupCalled, $plugin) {
    $setupCalled = true;
    expect($p)->toBe($plugin);
  });

  expect($setupCalled)->toBeTrue();
});

test('addPlugin() does not call init() before _initPlugins()', function () {
  $api = new Api();
  $plugin = createTestPluginA();

  $api->addPlugin($plugin);

  expect($plugin->initialized)->toBeFalse();
});

test('addPlugin() calls init() immediately after _initPlugins()', function () {
  $api = new Api();
  $api->_initPlugins();

  $plugin = createTestPluginA();
  $api->addPlugin($plugin);

  expect($plugin->initialized)->toBeTrue();
});

test('hasPlugin() and getPlugin()', function () {
  $api = new Api();
  $plugin = createTestPluginA();

  expect($api->hasPlugin($plugin::class))->toBeFalse();
  expect($api->getPlugin($plugin::class))->toBeNull();

  $api->addPlugin($plugin);

  expect($api->hasPlugin($plugin::class))->toBeTrue();
  expect($api->getPlugin($plugin::class))->toBe($plugin);
});

test('getPlugins() returns all plugins', function () {
  $api = new Api();
  $api->addPlugin(createTestPluginA());
  $api->addPlugin(createTestPluginB());

  expect($api->getPlugins())->toHaveCount(2);
});
