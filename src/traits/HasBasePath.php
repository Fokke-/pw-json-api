<?php

namespace PwJsonApi;

/**
 * Provides $basePath property with getter and setter functions.
 */
trait HasBasePath
{
	use Utils;

	/**
	 * Base path of the instance
	 */
	private string|null $basePath = null;

	/**
	 * Get base path of the instance
	 */
	public function getBasePath(): string|null
	{
		return $this->basePath;
	}

	/**
	 * Set base path for instance
	 */
	public function setBasePath(string|null $path): static
	{
		if (is_null($path)) {
			$this->basePath = $path;
			return $this;
		}

		$path = $this->formatPath($path);
		if (empty($path)) {
			$path = null;
		}

		$this->basePath = $path;
		return $this;
	}
}
