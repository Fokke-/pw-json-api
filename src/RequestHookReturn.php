<?php

namespace PwJsonApi;

/**
 * Base class for request hooks
 *
 * @see https://pwjsonapi.fokke.fi/request-hooks.html#hook-arguments
 */
abstract class RequestHookReturn
{
  /** Request */
  public Request $request;

  /** ProcessWire URL hook event */
  public \ProcessWire\HookEvent $event;

  /** Request endpoint */
  public Endpoint $endpoint;

  /** Request service */
  public Service $service;

  /** List of all parent services */
  public ServiceList $services;

  /** API instance */
  public Api $api;
}
