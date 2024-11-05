<?php

namespace Exn\LaravelHtmx;

use Exn\LaravelHtmx\Internal\HxRequest as InternalHxRequest;
use Illuminate\Http\Request;

class HxRequest
{
  /**
   * Checking if HX-Request header exists
   *
   * @param mixed $request
   * @return bool
   */
  public static function hx(): bool
  {
    return InternalHxRequest::hx(request());
  }

  /**
   * Checking if HX-Boosted header is set to "true"
   *
   * @param mixed $request
   * @return bool
   */
  public static function hxBoosted(): bool
  {
    return InternalHxRequest::hxBoosted(request());
  }

  /**
   * Returning a value of HX-Current-URL header (current url of the browser)
   *
   * @param mixed $request
   * @return string|null
   */
  public static function hxCurrentUrl(): string|null
  {
    return InternalHxRequest::hxCurrentUrl(request());
  }

  /**
   * Returning a value of HX-Target header (id of a target element if it exists)
   *
   * @param mixed $request
   * @return string|null
   */
  public static function hxTarget(): string|null
  {
    return InternalHxRequest::hxTarget(request());
  }

  /**
   * Returning a value of HX-Trigger header (id of a trigger element if it exists)
   *
   * @param mixed $request
   * @return string|null
   */
  public static function hxTrigger(): string|null
  {
    return InternalHxRequest::hxTrigger(request());
  }

  /**
   * Returning a value of HX-Trigger-Name header (name of a target element if it exists)
   *
   * @param mixed $request
   * @return string|null
   */
  public static function hxTriggerName(): string|null
  {
    return InternalHxRequest::hxTriggerName(request());
  }

  /**
   * Returning a value of HX-Prompt header (the user response to an hx-prompt)
   *
   * @param mixed $request
   * @return string|null
   */
  public static function hxPrompt(): string|null
  {
    return InternalHxRequest::hxPrompt(request());
  }

  /**
   * Checks if HX-History-Restore-Request is set to "true" (if the request is for history restoration after a miss in the local history cache)
   *
   * @param mixed $request
   * @return bool
   */
  public static function hxHistoryRestoreRequest(): bool
  {
    return InternalHxRequest::hxHistoryRestoreRequest(request());
  }

  public static function hxSetValidationFailView(string $viewName, ?array $data = null): Request
  {
    return InternalHxRequest::hxSetValidationFailView(request(), $viewName, $data);
  }

  public static function hxValidationFailView(): string|array|null
  {
    return InternalHxRequest::hxValidationFailView(request());
  }
}
