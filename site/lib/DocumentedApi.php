<?php namespace ProcessWire;

use PwJsonApi\Api;
use OpenApi\Attributes as OA;

#[
  OA\Info(
    title: 'Documented API',
    version: '1.0.0',
    description: 'Example API with OpenAPI documentation',
  ),
]
class DocumentedApi extends Api {}
