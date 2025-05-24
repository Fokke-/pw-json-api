<?php

namespace PwJsonApi;

class HookReturn
{
  /** ProcessWire hook event */
  public \ProcessWire\HookEvent $event;

  /** Request method */
  public string $method;

  /** Request endpoint */
  public Endpoint $endpoint;

  /** Request service */
  public Service $service;
}
