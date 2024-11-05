<?php

namespace Exn\LaravelHtmx\Internal;

use Exn\LaravelHtmx\Constants\HxResponseConstants;
use Illuminate\Http\Response;


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
  public static function hxLocation(Response $response, string $location): Response
  {
    $response->header(
      HxResponseConstants::HX_LOCATION_HEADER,
      is_array($location) ? json_encode($location) : $location,
    );

    return $response;
  }

  /**
   * Sets header for HX-Redirect (can be used to do a client-side redirect to a new location)
   *
   * @param string $redirect
   * @param mixed $response
   * @return \Illuminate\Http\Response
   */
  public static function hxRedirect(Response $response, string $redirect): Response
  {
    $response->header(HxResponseConstants::HX_REDIRECT_HEADER, $redirect);

    return $response;
  }

  /**
   * Sets header for HX-Push-Url (pushes a new url into the history stack)
   *
   * @param string|bool $pushUrl - URL to be pushed on history stack or false to prevent pushing
   * @param mixed $response
   * @return \Illuminate\Http\Response
   */
  public static function hxPushUrl(Response $response, string|bool $pushUrl): Response
  {
    $response->header(
      HxResponseConstants::HX_PUSH_URL_HEADER,
      is_string($pushUrl) ? $pushUrl : json_encode($pushUrl),
    );

    return $response;
  }

  /**
   * Set header for Hx-Replace-Url (replaces the current URL in the location bar)
   *
   * @param string|bool $replaceUrl- URL to be replaced on history stack or false to prevent replacing
   * @param mixed $response
   * @return \Illuminate\Http\Response
   */
  public static function hxReplaceUrl(Response $response, string|bool $replaceUrl): Response
  {
    $response->header(
      HxResponseConstants::HX_REPLACE_URL_HEADER,
      is_string($replaceUrl) ? $replaceUrl : json_encode(($replaceUrl))
    );

    return $response;
  }

  /**
   * Set header for Hx-Refresh (if set to “true” the client-side will do a full refresh of the page)
   *
   * @param bool $refresh - default is "true"
   * @param mixed $response
   * @return \Illuminate\Http\Response
   */
  public static function hxRefresh(Response $response, ?bool $refresh = true): Response
  {
    $response->header(HxResponseConstants::HX_REFRESH_HEADER, json_encode($refresh));

    return $response;
  }

  /**
   * Set header for HX-Reswap (allows you to specify how the response will be swapped. See hx-swap for possible values)
   *
   * @param string $swap
   * @param mixed $response
   * @return \Illuminate\Http\Response
   */
  public static function hxReswap(Response $response, string $swap): Response
  {
    $response->header(HxResponseConstants::HX_RESWAP_HEADER, $swap);

    return $response;
  }

  /**
   * Set header for HX-Retarget (a CSS selector that updates the target of the content update to a different element on the page)
   *
   * @param string $target
   * @param mixed $response
   * @return \Illuminate\Http\Response
   */
  public static function hxRetarget(Response $response, string $target): Response
  {
    $response->header(HxResponseConstants::HX_RETARGET_HEADER, $target);

    return $response;
  }

  /**
   * Set header for HX-Reselect (a CSS selector that allows you to choose which part of the response is used to be swapped in. Overrides an existing hx-select on the triggering element)
   *
   * @param string $select
   * @param mixed $response
   * @return \Illuminate\Http\Response
   */
  public static function hxReselect(Response $response, string $select): Response
  {
    $response->header(HxResponseConstants::HX_RESELECT_HEADER, $select);

    return $response;
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
  public static function hxTrigger(Response $response, string|array $trigger): Response
  {
    $response->header(
      HxResponseConstants::HX_TRIGGER_HEADER,
      is_string($trigger) ? $trigger : json_encode($trigger),
    );

    return $response;
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
  public static function hxTriggerAfterSettle(Response $response, string|array $trigger): Response
  {
    $response->header(
      HxResponseConstants::HX_TRIGGER_AFTER_SETTLE_HEADER,
      is_string($trigger) ? $trigger : json_encode($trigger),
    );

    return $response;
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
  public static function hxTriggerAfterSwap(Response $response, string|array $trigger): Response
  {
    $response->header(
      HxResponseConstants::HX_TRIGGER_AFTER_SWAP_HEADER,
      is_string($trigger) ? $trigger : json_encode($trigger),
    );

    return $response;
  }
}
