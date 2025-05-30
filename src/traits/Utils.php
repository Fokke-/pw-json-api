<?php

// TODO: move to class, as this is not easily testable
namespace PwJsonApi;

/**
 * Utilities
 */
trait Utils
{
  private function formatPath(string|null $path): string|null
  {
    // For empty or root path, return null
    if (empty($path) || $path === '/') {
      return null;
    }

    // Transform to lowercase
    $path = mb_strtolower($path);

    // Replace multiple slashes with a single slash
    $path = preg_replace('#/+#', '/', $path);

    // Remove leading slash
    $path = ltrim($path, '/');

    // Remove trailing slash
    $path = rtrim($path, '/');

    return $path;
  }
}
