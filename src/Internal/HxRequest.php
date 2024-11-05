<?php

namespace Exn\LaravelHtmx\Internal;

use Exn\LaravelHtmx\Constants\HxRequestConstants;
use Illuminate\Http\Request;

class HxRequest
{
  /**
   * Checking if HX-Request header exists
   *
   * @param mixed $request
   * @return bool
   */
  public static function hx(Request $request): bool
  {
    return $request->hasHeader(HxRequestConstants::HX_REQUEST_HEADER);
  }

  /**
   * Checking if HX-Boosted header is set to "true"
   *
   * @param mixed $request
   * @return bool
   */
  public static function hxBoosted(Request $request): bool
  {
    return $request->header(HxRequestConstants::HX_BOOSTED_HEADER) == 'true';
  }

  /**
   * Returning a value of HX-Current-URL header (current url of the browser)
   *
   * @param mixed $request
   * @return string|null
   */
  public static function hxCurrentUrl(Request $request): string|null
  {
    return $request->header(HxRequestConstants::HX_CURRENT_URL_HEADER);
  }

  /**
   * Returning a value of HX-Target header (id of a target element if it exists)
   *
   * @param mixed $request
   * @return string|null
   */
  public static function hxTarget(Request $request): string|null
  {
    return $request->header(HxRequestConstants::HX_TARGET_HEADER);
  }

  /**
   * Returning a value of HX-Trigger header (id of a trigger element if it exists)
   *
   * @param mixed $request
   * @return string|null
   */
  public static function hxTrigger(Request $request): string|null
  {
    return $request->header(HxRequestConstants::HX_TRIGGER_HEADER);
  }

  /**
   * Returning a value of HX-Trigger-Name header (name of a target element if it exists)
   *
   * @param mixed $request
   * @return string|null
   */
  public static function hxTriggerName(Request $request): string|null
  {
    return $request->header(HxRequestConstants::HX_TRIGGER_NAME_HEADER);
  }

  /**
   * Returning a value of HX-Prompt header (the user response to an hx-prompt)
   *
   * @param mixed $request
   * @return string|null
   */
  public static function hxPrompt(Request $request): string|null
  {
    return $request->header(HxRequestConstants::HX_PROMPT_HEADER);
  }

  /**
   * Checks if HX-History-Restore-Request is set to "true" (if the request is for history restoration after a miss in the local history cache)
   *
   * @param mixed $request
   * @return bool
   */
  public static function hxHistoryRestoreRequest(Request $request): bool
  {
    return $request->header(HxRequestConstants::HX_HISTORY_RESTORE_REQUEST) == 'true';
  }

  public static function hxSetValidationFailView(Request $request, string $viewName, ?array $data = null): Request
  {
    $request->merge([
      HxRequestConstants::_HX_ON_VALIDATION_FAIL_KEY => $data == null
        ? $viewName
        : ['view' => $viewName, 'data' => $data],
    ]);

    return $request;
  }

  public static function hxValidationFailView(Request $request): string|array|null
  {
    return $request->input(HxRequestConstants::_HX_ON_VALIDATION_FAIL_KEY, null);
  }
}
