<?php

namespace PwJsonApi;

/**
 * Authentication handler arguments
 *
 * @see https://pwjsonapi.fokke.fi/authentication-overview.html
 */
class AuthenticateArgs
{
  /** Request */
  public Request $request;

  /** ProcessWire user */
  public \ProcessWire\User $user;

  /** ProcessWire URL hook event */
  public \ProcessWire\HookEvent $event;
}
