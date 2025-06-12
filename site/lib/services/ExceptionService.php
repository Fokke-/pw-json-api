<?php namespace ProcessWire;

use PwJsonApi\Api404Exception;
use PwJsonApi\ApiException;
use PwJsonApi\Service;

class ExceptionService extends Service
{
  public function __construct()
  {
    parent::__construct();

    $this->addEndpoint('/')->get(function () {
      throw new ApiException('This was doomed to fail!');
    });

    $this->addEndpoint('/custom-code')->get(function () {
      throw (new ApiException('This was doomed to fail!'))->code(401);
    });

    $this->addEndpoint('/without-message')->get(function () {
      throw new ApiException();
    });

    $this->addEndpoint('/404')->get(function () {
      throw new Api404Exception();
    });
  }
}
