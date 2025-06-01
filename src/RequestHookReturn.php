<?php

namespace PwJsonApi;

/**
 * Base class for request hooks
 */
abstract class RequestHookReturn
{
  /** ProcessWire URL hook event */
  public \ProcessWire\HookEvent $event;

  /** Request method */
  public string $method;

  /** Request endpoint */
  public Endpoint $endpoint;

  /** Request service */
  public Service $service;

  /** List of all parent services */
  public ServiceList $services;
}
