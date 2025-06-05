<?php

namespace PwJsonApi;

/**
 * Allowed request methods
 */
enum RequestMethod: string
{
  case Get = 'GET';
  case Head = 'HEAD';
  case Options = 'OPTIONS';
  case Put = 'PUT';
  case Delete = 'DELETE';
  case Post = 'POST';
  case Patch = 'PATCH';
}
