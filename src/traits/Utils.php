<?php

namespace PwJsonApi;

/**
 * Utilities
 */
trait Utils
{
  /**
   * Format path: single slashes, no leading or trailing slash
   *
   * The path is lowercased unless $preserveCase is true.
   */
  private function formatPath(
    string|null $path,
    bool $preserveCase = false,
  ): string|null {
    // For empty or root path, return null
    if (empty($path) || $path === '/') {
      return null;
    }

    // Transform to lowercase
    if (!$preserveCase) {
      $path = mb_strtolower($path);
    }

    // Replace multiple slashes with a single slash
    $path = (string) preg_replace('#/+#', '/', $path);

    // Remove leading slash
    $path = ltrim($path, '/');

    // Remove trailing slash
    $path = rtrim($path, '/');

    return $path;
  }
}
