<?php

use ProcessWire\{FoodService, FruitService, WireException};
use PwJsonApi\{Api, Service, Endpoint};
use PwJsonApi\Plugins\ApiPlugin;

// --- Service locking ---

test('service is not locked after addService()', function () {
  $api = new Api();
  $api->addService(new FoodService());

  $service = $api->getService('FoodService');
  expect($service->_isLocked())->toBeFalse();
});

test('service is locked after run()', function () {
  $api = new Api();
  $api->addService(new FoodService());
  $api->run();

  $service = $api->getService('FoodService');
  expect($service->_isLocked())->toBeTrue();
});

test('service is not locked during init()', function () {
  $wasLocked = null;

  $service = new class ($wasLocked) extends Service {
    /** @var bool|null */
    private $ref;

    public function __construct(bool|null &$wasLocked)
    {
      parent::__construct();
      $this->ref = &$wasLocked;
    }

    protected function init()
    {
      $this->ref = $this->_isLocked();
    }
  };

  $api = new Api();
  $api->addService($service);

  expect($wasLocked)->toBeFalse();
});

test('service is not locked during setup callback', function () {
  $wasLocked = null;

  $api = new Api();
  $api->addService(new FoodService(), function ($service) use (&$wasLocked) {
    $wasLocked = $service->_isLocked();
  });

  expect($wasLocked)->toBeFalse();
});

test('locked service rejects addEndpoint()', function () {
  $api = new Api();
  $api->addService(new FoodService());
  $api->run();

  $service = $api->getService('FoodService');
  $service->addEndpoint('/fail');
})->throws(WireException::class, 'Cannot add endpoint');

test('locked service rejects removeEndpoint()', function () {
  $api = new Api();
  $api->addService(new FruitService());
  $api->run();

  $service = $api->getService('FruitService');
  $service->removeEndpoint('/');
})->throws(WireException::class, 'Cannot remove endpoint');

test('locked service rejects addService()', function () {
  $api = new Api();
  $api->addService(new FoodService());
  $api->run();

  $service = $api->getService('FoodService');
  $service->addService(new FruitService());
})->throws(WireException::class, 'Cannot add service');

test('locked service rejects addPlugin()', function () {
  $api = new Api();
  $api->addService(new FoodService());
  $api->run();

  $service = $api->getService('FoodService');
  $service->addPlugin(
    new class extends ApiPlugin {
      public function init(Api|Service|Endpoint $context): static
      {
        parent::init($context);
        return $this;
      }
    },
  );
})->throws(WireException::class, 'Cannot add plugin');

// --- Api locking ---

test('api is locked after run()', function () {
  $api = new Api();
  $api->addService(new FoodService());
  $api->run();

  expect($api->_isLocked())->toBeTrue();
});

test('locked api rejects addService()', function () {
  $api = new Api();
  $api->run();

  $api->addService(new FoodService());
})->throws(WireException::class, 'Cannot add service');

test('locked api rejects addPlugin()', function () {
  $api = new Api();
  $api->run();

  $api->addPlugin(
    new class extends ApiPlugin {
      public function init(Api|Service|Endpoint $context): static
      {
        parent::init($context);
        return $this;
      }
    },
  );
})->throws(WireException::class, 'Cannot add plugin');

// --- Endpoint locking ---

test('endpoint is locked after run()', function () {
  $api = new Api();
  $api->addService(new FruitService());
  $api->run();

  $endpoint = $api->findEndpoint('/fruits');
  expect($endpoint->_isLocked())->toBeTrue();
});

test('locked endpoint rejects get()', function () {
  $api = new Api();
  $api->addService(new FruitService());
  $api->run();

  $endpoint = $api->findEndpoint('/fruits');
  $endpoint->get(function () {});
})->throws(WireException::class, 'Cannot set GET handler');

test('locked endpoint rejects post()', function () {
  $api = new Api();
  $api->addService(new FruitService());
  $api->run();

  $endpoint = $api->findEndpoint('/fruits');
  $endpoint->post(function () {});
})->throws(WireException::class, 'Cannot set POST handler');

test('locked endpoint rejects put()', function () {
  $api = new Api();
  $api->addService(new FruitService());
  $api->run();

  $endpoint = $api->findEndpoint('/fruits');
  $endpoint->put(function () {});
})->throws(WireException::class, 'Cannot set PUT handler');

test('locked endpoint rejects patch()', function () {
  $api = new Api();
  $api->addService(new FruitService());
  $api->run();

  $endpoint = $api->findEndpoint('/fruits');
  $endpoint->patch(function () {});
})->throws(WireException::class, 'Cannot set PATCH handler');

test('locked endpoint rejects delete()', function () {
  $api = new Api();
  $api->addService(new FruitService());
  $api->run();

  $endpoint = $api->findEndpoint('/fruits');
  $endpoint->delete(function () {});
})->throws(WireException::class, 'Cannot set DELETE handler');

test('locked endpoint rejects head()', function () {
  $api = new Api();
  $api->addService(new FruitService());
  $api->run();

  $endpoint = $api->findEndpoint('/fruits');
  $endpoint->head(function () {});
})->throws(WireException::class, 'Cannot set HEAD handler');

test('locked endpoint rejects addPlugin()', function () {
  $api = new Api();
  $api->addService(new FruitService());
  $api->run();

  $endpoint = $api->findEndpoint('/fruits');
  $endpoint->addPlugin(
    new class extends ApiPlugin {
      public function init(Api|Service|Endpoint $context): static
      {
        parent::init($context);
        return $this;
      }
    },
  );
})->throws(WireException::class, 'Cannot add plugin');

// --- Hooks are also locked ---

test('locked service rejects hookBefore()', function () {
  $api = new Api();
  $api->addService(new FoodService());
  $api->run();

  $service = $api->getService('FoodService');
  $service->hookBefore(function () {});
})->throws(WireException::class, 'Cannot add hook');

test('locked service rejects hookAfter()', function () {
  $api = new Api();
  $api->addService(new FoodService());
  $api->run();

  $service = $api->getService('FoodService');
  $service->hookAfter(function () {});
})->throws(WireException::class, 'Cannot add hook');

test('locked service rejects hookOnError()', function () {
  $api = new Api();
  $api->addService(new FoodService());
  $api->run();

  $service = $api->getService('FoodService');
  $service->hookOnError(function () {});
})->throws(WireException::class, 'Cannot add hook');

test('locked api rejects hookBefore()', function () {
  $api = new Api();
  $api->run();

  $api->hookBefore(function () {});
})->throws(WireException::class, 'Cannot add hook');

test('locked endpoint rejects hookBefore()', function () {
  $api = new Api();
  $api->addService(new FruitService());
  $api->run();

  $endpoint = $api->findEndpoint('/fruits');
  $endpoint->hookBefore(function () {});
})->throws(WireException::class, 'Cannot add hook');
