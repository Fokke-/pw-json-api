<?php

namespace PwJsonApi;

/**
 * Endpoint handler arguments
 *
 * @see https://pwjsonapi.fokke.fi/endpoints.html#endpoint-handler-arguments
 */
class EndpointHandlerArgs
{
  /** Request */
  public Request $request;

  /** ProcessWire user */
  public \ProcessWire\User $user;

  /** ProcessWire URL hook event */
  public \ProcessWire\HookEvent $event;
}
