<?php

namespace PwJsonApi;

/**
 * Exception handler arguments
 *
 * @see https://pwjsonapi.fokke.fi/api-instance.html#exception-handling
 */
class ExceptionHandlerArgs
{
  /** Exception */
  public \Throwable $exception;

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
