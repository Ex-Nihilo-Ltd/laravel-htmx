<?php
/**
 * This file should not be used, it won't work properly and would produce errors
 */

namespace Exn\LaravelHtmx;

use Exn\LaravelHtmx\Internal\HxResponse as InternalHxResponse;
use Illuminate\Http\Response;


/**
 * @deprecated This class should not be used
 */
class HxResponse
{
  /**
   * Sets header for HX-Location (allows you to do a client-side redirect that does not do a full page reload)
   *
   * Keep in mind that status returned with this header should not be 3xx but rather 2xx
   *
   * @param string|array $location - URL path to redirect or "settings" object (@see https://htmx.org/headers/hx-location/)
   * @param mixed $response
   * @return \Illuminate\Http\Response
   */
  public static function hxLocation(string $location): Response
  {
    return InternalHxResponse::hxLocation(response(), $location);
  }

  /**
   * Sets header for HX-Redirect (can be used to do a client-side redirect to a new location)
   *
   * @param string $redirect
   * @param mixed $response
   * @return \Illuminate\Http\Response
   */
  public static function hxRedirect(string $redirect): Response
  {
    return InternalHxResponse::hxRedirect(response(), $redirect);
  }

  /**
   * Sets header for HX-Push-Url (pushes a new url into the history stack)
   *
   * @param string|bool $pushUrl - URL to be pushed on history stack or false to prevent pushing
   * @param mixed $response
   * @return \Illuminate\Http\Response
   */
  public static function hxPushUrl(string|bool $pushUrl): Response
  {
    return InternalHxResponse::hxPushUrl(response(), $pushUrl);
  }

  /**
   * Set header for Hx-Replace-Url (replaces the current URL in the location bar)
   *
   * @param string|bool $replaceUrl- URL to be replaced on history stack or false to prevent replacing
   * @param mixed $response
   * @return \Illuminate\Http\Response
   */
  public static function hxReplaceUrl(string|bool $replaceUrl): Response
  {
    return InternalHxResponse::hxReplaceUrl(response(), $replaceUrl);
  }

  /**
   * Set header for Hx-Refresh (if set to “true” the client-side will do a full refresh of the page)
   *
   * @param bool $refresh - default is "true"
   * @param mixed $response
   * @return \Illuminate\Http\Response
   */
  public static function hxRefresh(?bool $refresh = true): Response
  {
    return InternalHxResponse::hxRefresh(response(), $refresh);
  }

  /**
   * Set header for HX-Reswap (allows you to specify how the response will be swapped. See hx-swap for possible values)
   *
   * @param string $swap
   * @param mixed $response
   * @return \Illuminate\Http\Response
   */
  public static function hxReswap(string $swap): Response
  {
    return InternalHxResponse::hxReswap(response(), $swap);
  }

  /**
   * Set header for HX-Retarget (a CSS selector that updates the target of the content update to a different element on the page)
   *
   * @param string $target
   * @param mixed $response
   * @return \Illuminate\Http\Response
   */
  public static function hxRetarget(string $target): Response
  {
    return InternalHxResponse::hxRetarget(response(), $target);
  }

  /**
   * Set header for HX-Reselect (a CSS selector that allows you to choose which part of the response is used to be swapped in. Overrides an existing hx-select on the triggering element)
   *
   * @param string $select
   * @param mixed $response
   * @return \Illuminate\Http\Response
   */
  public static function hxReselect(string $select): Response
  {
    return InternalHxResponse::hxReselect(response(), $select);
  }

  /**
   * Wrapper around setting HX-Trigger header (allows you to trigger client-side events)
   *
   * You can format trigger(s) in PHP syntax (e.g. ['showMessage' => ['target' => '#otherElement']]) and it would
   * be properly formatted to the HX-Trigger header
   *
   * @param string|array $trigger - if it's an object it will be json encoded
   * @param mixed $response
   * @return \Illuminate\Http\Response
   */
  public static function hxTrigger(string|array $trigger): Response
  {
    return InternalHxResponse::hxTrigger(response(), $trigger);
  }

  /**
   * Wrapper around setting HX-Trigger-After-Settle header (allows you to trigger client-side events)
   *
   * You can format trigger(s) in PHP syntax (e.g. ['showMessage' => ['target' => '#otherElement']]) and it would
   * be properly formatted to the HX-Trigger-After-Settle header
   *
   * @param string|array $trigger - if it's an object it will be json encoded
   * @param mixed $response
   * @return \Illuminate\Http\Response
   */
  public static function hxTriggerAfterSettle(string|array $trigger): Response
  {
    return InternalHxResponse::hxTriggerAfterSettle(response(), $trigger);
  }

  /**
   * Wrapper around setting HX-Trigger-After-Swap header (allows you to trigger client-side events)
   *
   * You can format trigger(s) in PHP syntax (e.g. ['showMessage' => ['target' => '#otherElement']]) and it would
   * be properly formatted to the HX-Trigger-After-Swap header
   *
   * @param string|array $trigger - if it's an object it will be json encoded
   * @param mixed $response
   * @return \Illuminate\Http\Response
   */
  public static function hxTriggerAfterSwap(string|array $trigger): Response
  {
    return InternalHxResponse::hxTriggerAfterSwap(response(), $trigger);
  }
}
