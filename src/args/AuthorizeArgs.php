<?php

namespace PwJsonApi;

/**
 * Authorization handler arguments
 *
 * @see https://pwjsonapi.fokke.fi/authentication-overview.html#authorization
 */
class AuthorizeArgs
{
  /** Request */
  public Request $request;

  /** ProcessWire user */
  public \ProcessWire\User $user;

  /** ProcessWire URL hook event */
  public \ProcessWire\HookEvent $event;
}
