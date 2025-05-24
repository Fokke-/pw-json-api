<?php

namespace PwJsonApi;

class Service
{
	use Utils;
	use HasEndpointList;
	use HasHooks;

	/** Service name */
	public readonly string $name;

	/** Constructor */
	public function __construct()
	{
		$this->name = (new \ReflectionClass($this))->getShortName();
	}
}
