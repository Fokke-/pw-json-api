<?php

use ProcessWire\WireException;
use PwJsonApi\Api;
use PwJsonApi\Plugins\CSRFPlugin;

test('setupService() returns static for fluent interface', function () {
  $plugin = new CSRFPlugin();

  expect($plugin->setupService(function ($service) {}))->toBe($plugin);
});

test('setupService() configures the token service', function () {
  $api = new Api();
  $api->addPlugin(new CSRFPlugin(), function ($plugin) {
    $plugin->setupService(function ($service) {
      $service->skipAuthentication();
    });
  });
  $api->run();

  $service = $api->getService('CSRFPluginService');

  expect($service->_skipsAuthentication())->toBeTrue();
});

test('token service does not skip authentication by default', function () {
  $api = new Api();
  $api->addPlugin(new CSRFPlugin());
  $api->run();

  $service = $api->getService('CSRFPluginService');

  expect($service->_skipsAuthentication())->toBeFalse();
  expect($service->_skipsAuthorization())->toBeFalse();
});

test('initialized plugin rejects setupService()', function () {
  $api = new Api();
  $api->addPlugin(new CSRFPlugin());
  $api->run();

  $api->getPlugin(CSRFPlugin::class)->setupService(function ($service) {});
})->throws(WireException::class, 'Cannot set up service');
