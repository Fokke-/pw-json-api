<?php

namespace PwJsonApi;

/**
 * Base class for request hooks
 */
abstract class RequestHookReturn
{
  /** Request object */
  public Request $request;

  /**
   * ProcessWire URL hook event
   *
   * @deprecated 1.2.0 Use $request->event instead
   */
  public \ProcessWire\HookEvent $event;

  /**
   * Request method
   *
   * @deprecated 1.2.0 Use $request->method instead
   */
  public string $method;

  /** Request endpoint */
  public Endpoint $endpoint;

  /** Request service */
  public Service $service;

  /** List of all parent services */
  public ServiceList $services;

  /** API instance */
  public Api $api;
}
