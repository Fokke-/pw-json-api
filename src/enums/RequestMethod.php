<?php

namespace PwJsonApi;

/**
 * Allowed request methods
 */
enum RequestMethod: string
{
	case Get = 'GET';
	case Post = 'POST';
	case Head = 'HEAD';
	case Put = 'PUT';
	case Delete = 'DELETE';
	case Options = 'OPTIONS';
}
