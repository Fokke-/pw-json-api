<?php namespace ProcessWire;

use PwJsonApi\{AuthenticateArgs, AuthorizeArgs, Response, Service};

class AuthContextChildService extends Service
{
  /** @var array<string, string> */
  public static array $authenticateArgs = [];

  /** @var array<string, string> */
  public static array $authorizeArgs = [];

  /**
   * Describe argument object properties by class name
   *
   * @return array<string, string>
   */
  public static function describeArgs(
    AuthenticateArgs|AuthorizeArgs $args,
  ): array {
    return [
      'type' => get_class($args),
      'request' => get_class($args->request),
      'user' => get_class($args->user),
      'endpoint' => get_class($args->endpoint),
      'service' => get_class($args->service),
      'services' => get_class($args->services),
      'api' => get_class($args->api),
    ];
  }

  protected function init()
  {
    $this->setBasePath('/child');

    $this->addEndpoint('/')->get(function ($args) {
      return new Response([
        'authenticate_args' => self::$authenticateArgs,
        'authorize_args' => self::$authorizeArgs,
      ]);
    });
  }
}
