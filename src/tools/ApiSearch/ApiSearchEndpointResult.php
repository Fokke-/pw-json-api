<?php

namespace PwJsonApi;

/**
 * Result of a successful endpoint search
 */
class ApiSearchEndpointResult
{
  /**
   * Endpoint
   */
  public Endpoint $endpoint;

  /**
   * Service
   */
  public Service $service;

  /**
   * Service sequence of the result
   *
   * @var Service[]
   */
  public array $serviceSequence;

  /**
   * Constructor
   */
  public function __construct(
    Endpoint $endpoint,
    Service $service,
    array $serviceSequence,
  ) {
    $this->endpoint = $endpoint;
    $this->service = $service;
    $this->serviceSequence = $serviceSequence;
  }

  /**
   * Resolve path for the endpoint
   */
  public function resolvePath(?string $basePath = null): string
  {
    return implode(
      '/',
      array_filter(
        [
          // For leading slash
          '',

          // Base path
          $basePath,

          // Service tree base paths
          ...array_reduce(
            $this->serviceSequence,
            function ($acc, $service) {
              $acc[] = $service->getBasePath();
              return $acc;
            },
            [],
          ),

          // Endpoint path
          $this->endpoint->getPath(),
        ],
        fn($segment) => !is_null($segment),
      ),
    );
  }

  /**
   * Resolve hooks from result endpoint and all of it's services
   *
   * For timing "Before" the order is Service -> Endpoint
   * For timing "After" the order is Endpoint -> Service
   *
   * @return callable[]
   */
  public function resolveHooks(
    HookTiming $timing,
    RequestMethod|null $requestMethod = null,
  ): array {
    $serviceHooks = array_reduce(
      $timing === HookTiming::Before
        ? $this->serviceSequence
        : array_reverse($this->serviceSequence),
      function ($acc, $service) use ($timing, $requestMethod) {
        $acc = [
          ...$acc,
          ...$service->findRequestHooks($timing),
          ...$service->findRequestHooks($timing, $requestMethod),
        ];

        return $acc;
      },
      [],
    );

    $endpointHooks = [
      ...$this->endpoint->findRequestHooks($timing),
      ...$this->endpoint->findRequestHooks($timing, $requestMethod),
    ];

    if ($timing === HookTiming::Before) {
      return [...$serviceHooks, ...$endpointHooks];
    }

    return [...$endpointHooks, ...$serviceHooks];
  }

  /**
   * Resolve onError hooks from result endpoint and all of it's services
   */
  public function resolveErrorHooks(): array
  {
    $serviceHooks = array_reduce(
      array_reverse($this->serviceSequence),
      function ($acc, $service) {
        $acc = [...$acc, ...$service->getRequestHooks(RequestHookKey::OnError)];

        return $acc;
      },
      [],
    );

    $endpointHooks = [
      ...$this->endpoint->getRequestHooks(RequestHookKey::OnError),
    ];

    return [...$endpointHooks, ...$serviceHooks];
  }
}
