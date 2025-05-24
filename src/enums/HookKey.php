<?php

namespace PwJsonApi;

/**
 * Available hook keys
 */
enum HookKey
{
	/** Before any request */
	case Before;

	/** After any request */
	case After;

	/** Before GET request */
	case BeforeGet;

	/** After GET request */
	case AfterGet;

	/** Before POST request */
	case BeforePost;

	/** After POST request */
	case AfterPost;

	/** Before HEAD request */
	case BeforeHead;

	/** After HEAD request */
	case AfterHead;

	/** Before PUT request */
	case BeforePut;

	/** After PUT request */
	case AfterPut;

	/** Before DELETE request */
	case BeforeDelete;

	/** After DELETE request */
	case AfterDelete;

	/** Before OPTIONS request */
	case BeforeOptions;

	/** After OPTIONS request */
	case AfterOptions;
}
