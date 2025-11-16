<?php

namespace PwJsonApi;

use PwJsonApi\RequestMethod;
use ProcessWire\{HookEvent};

/**
 * API request
 */
class Request
{
  /** Request method */
  public string $method;

  /** Method as enum */
  public RequestMethod|null $methodEnum;

  /** Requested path */
  public string|null $path;

  /**
   * Route parameters of dynamic paths
   *
   * @var array<string|int, string>
   * @see https://fokke-.github.io/pw-json-api/endpoints.html#dynamic-paths
   */
  public array $routeParams;

  /**
   * Query parameters
   *
   * @var array<string, mixed>
   */
  public array $queryParams;

  /**
   * Headers
   *
   * @var array<string, string>
   */
  public array $headers;

  /** Content-Type header */
  public string|null $contentType;

  /** Accept header */
  public string|null $accept;

  /**
   * Shorthand for $_COOKIES
   *
   * @var array<string, string>
   */
  public array $cookies;

  /** Shorthand for $_SERVER['REMOTE_ADDR'] */
  public string|null $ip;

  /** Shorthand for $_SERVER['HTTP_USER_AGENT'] */
  public string|null $userAgent;

  /** Shorthand for $_SERVER['SERVER_PROTOCOL'] */
  public string|null $protocol;

  /** ProcessWire URL hook event */
  public HookEvent $event;

  /** Request body */
  public mixed $body;

  /**
   * Normalized value of $_FILES
   *
   * @var array<string, list<array<string, mixed>>>
   */
  public array $files;

  public function __construct()
  {
    $this->method = $this->getServerVar('REQUEST_METHOD') ?? '';
    $this->methodEnum = RequestMethod::tryFrom($this->method);
    $this->path = $this->getPath($this->getServerVar('REQUEST_URI'));
    $this->queryParams = $this->getQueryParams();
    $this->headers = $this->getHeaders();
    $this->contentType = $this->headers['Content-Type'] ?? null;
    $this->accept = $this->headers['Accept'] ?? null;
    $this->cookies = $this->getCookies();
    $this->ip = $this->getServerVar('REMOTE_ADDR');
    $this->userAgent = $this->getServerVar('HTTP_USER_AGENT');
    $this->protocol = $this->getServerVar('SERVER_PROTOCOL');
    $this->files = $this->getFiles();
  }

  protected function getServerVar(string $key): string|null
  {
    return isset($_SERVER[$key]) && is_string($_SERVER[$key])
      ? $_SERVER[$key]
      : null;
  }

  /**
   * Initialize request
   *
   * @internal
   */
  public function _init(HookEvent $event): static
  {
    $this->event = $event;
    $this->routeParams = $this->getRouteParams($event);
    $this->body = $this->getBody($this->contentType);
    return $this;
  }

  /**
   * Get query parameters
   *
   * @return array<string, mixed>
   */
  protected function getQueryParams(): array
  {
    return $_GET;
  }

  /**
   * Get cookies
   *
   * @return array<string, string>
   */
  protected function getCookies(): array
  {
    return array_filter($_COOKIE, fn($val) => is_string($val));
  }

  /**
   * Get route parameters
   *
   * @return array<string|int, string>
   */
  protected function getRouteParams(HookEvent $event): array
  {
    return $event->arguments;
  }

  /**
   * Get request path
   */
  protected function getPath(string|null $uri): string|null
  {
    if (empty($uri)) {
      return null;
    }

    $url = parse_url($uri, PHP_URL_PATH);
    if (empty($url)) {
      return null;
    }

    return rawurldecode($url);
  }

  /**
   * Get route parameter
   */
  public function routeParam(string $key): string|null
  {
    return $this->routeParams[$key] ?? null;
  }

  /**
   * Get query parameter
   *
   * @return mixed
   */
  public function queryParam(string $key): mixed
  {
    return $this->queryParams[$key] ?? null;
  }

  /**
   * Get route arguments
   *
   * @deprecated 1.2.0 Use params() instead.
   */
  public function arguments(string|null $key = null): mixed
  {
    // @phpstan-ignore argument.type
    return $this->event->arguments($key);
  }

  /**
   * Get headers
   *
   * @return array<string, string>
   */
  protected function getHeaders(): array
  {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    return array_filter($headers, fn($val) => is_string($val));
  }

  /**
   * Normalize media type (strip parameters like charset)
   */
  protected function getNormalizedContentType(
    string|null $contentType = null,
  ): string|null {
    return !empty($contentType)
      ? strtolower(trim(explode(';', $contentType)[0]))
      : null;
  }

  /**
   * Get body
   *
   * @throws ApiException If payload is malformed
   */
  protected function getBody(string|null $contentType = null): mixed
  {
    $contentType = $this->getNormalizedContentType($contentType);

    if ($contentType === 'application/json') {
      try {
        $input = file_get_contents('php://input');
        return json_decode(
          !empty($input) ? $input : '{}',
          true,
          512,
          JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING,
        );
      } catch (\JsonException) {
        throw new ApiException('Malformed request payload');
      }
    }

    return $_POST;
  }

  /**
   * Get files
   *
   * @return array<string, list<array<string, mixed>>>
   */
  protected function getFiles(): array
  {
    if (empty($_FILES)) {
      return [];
    }

    $out = [];

    foreach ($_FILES as $field => $data) {
      if (!is_array($data)) {
        continue;
      }

      $out[$field] = [];

      // Single file
      if (!is_array($data['name'])) {
        $out[$field][] = [
          'name' => $data['name'] ?? '',
          'full_path' => $data['full_path'] ?? '',
          'type' => $data['type'] ?? '',
          'tmp_name' => $data['tmp_name'] ?? '',
          'error' => $data['error'] ?? UPLOAD_ERR_NO_FILE,
          'size' => $data['size'] ?? 0,
        ];
        continue;
      }

      // Multiple files
      foreach ($data['name'] as $index => $name) {
        $out[$field][] = [
          'name' => isset($data['name'][$index]) ? $data['name'][$index] : '',
          'full_path' =>
            is_array($data['full_path']) && isset($data['full_path'][$index])
              ? $data['full_path'][$index]
              : '',
          'type' =>
            is_array($data['type']) && isset($data['type'][$index])
              ? $data['type'][$index]
              : '',
          'tmp_name' =>
            is_array($data['tmp_name']) && isset($data['tmp_name'][$index])
              ? $data['tmp_name'][$index]
              : '',
          'error' =>
            is_array($data['error']) && isset($data['error'][$index])
              ? $data['error'][$index]
              : UPLOAD_ERR_NO_FILE,
          'size' =>
            is_array($data['size']) && isset($data['size'][$index])
              ? $data['size'][$index]
              : 0,
        ];
      }
    }

    return $out;
  }

  /**
   * Return request data as an associative array
   *
   * @return array<string, mixed>
   */
  public function toArray(): array
  {
    $data = get_object_vars($this);
    return $data;
  }
}
