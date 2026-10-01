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

  /** Request endpoint */
  public Endpoint $endpoint;

  /** Request service */
  public Service $service;

  /** List of all parent services */
  public ServiceList $services;

  /** API instance */
  public Api $api;
}
