<?php

namespace PwJsonApi;

/**
 * Authentication handler arguments
 *
 * @see https://pwjsonapi.fokke.fi/authentication.html
 */
class AuthenticateArgs
{
  /** Request */
  public Request $request;

  /** ProcessWire URL hook event */
  public \ProcessWire\HookEvent $event;
}
