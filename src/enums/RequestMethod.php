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

  /**
   * Whether the method is safe (read-only) as defined in RFC 9110
   *
   * @see https://www.rfc-editor.org/rfc/rfc9110#section-9.2.1
   */
  public function isSafe(): bool
  {
    return match ($this) {
      self::Get, self::Head, self::Options => true,
      default => false,
    };
  }
}
