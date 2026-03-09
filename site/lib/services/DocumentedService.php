<?php namespace ProcessWire;

use PwJsonApi\{Service, Response, EndpointHandlerArgs};
use OpenApi\Attributes as OA;

class DocumentedService extends Service
{
  protected function init()
  {
    $this->addEndpoint('/greet')
      ->get($this->greet(...))
      ->post($this->createGreeting(...));
  }

  #[
    OA\Get(
      path: '/documented-api/greet',
      operationId: 'greet',
      parameters: [
        new OA\Parameter(
          name: 'name',
          in: 'query',
          required: false,
          schema: new OA\Schema(type: 'string'),
          description: 'Greeting message',
        ),
      ],
      responses: [
        new OA\Response(response: 200, description: 'Greeting response'),
      ],
    ),
  ]
  protected function greet(EndpointHandlerArgs $args): Response
  {
    return new Response([
      'greeting' => $args->request->queryParam('name') ?? null,
    ]);
  }

  #[
    OA\Post(
      path: '/documented-api/greet',
      operationId: 'createGreeting',
      requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
          properties: [
            new OA\Property(
              property: 'name',
              type: 'string',
              description: 'Greeting message',
            ),
          ],
        ),
      ),
      responses: [
        new OA\Response(response: 201, description: 'Greeting created'),
      ],
    ),
  ]
  protected function createGreeting(EndpointHandlerArgs $args): Response
  {
    return (new Response([
      'greeting' => $args->request->body['name'] ?? null,
    ]))->code(201);
  }
}
