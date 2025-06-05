<?php

namespace PwJsonApi;

/**
 * Request hook keys
 */
enum RequestHookKey
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

  /** Before PATCH request */
  case BeforePatch;

  /** After PATCH request */
  case AfterPatch;

  /** Before DELETE request */
  case BeforeDelete;

  /** After DELETE request */
  case AfterDelete;
}
