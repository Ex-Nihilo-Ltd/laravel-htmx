<?php

namespace Exn\LaravelHtmx\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HtmxExceptionRender
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $response = $next($request);
    if (!$request->hx()) {
      return $response;
    }

    $statusCode = $response->getStatusCode();

    if (
      $statusCode > config('htmx.errors.fullPageRerenderAboveStatus', 499)
      || in_array($statusCode, config('htmx.errors.fullPageRerenderOnStatus', []))
    ) {
      $response = $response
        ->hxRetarget('body')
        ->hxReswap('innerHTML')
        ->hxReselect(' ');
    }

    if (array_key_exists($statusCode, config('htmx.errors.customEventOnStatus', []))) {
      $customEvent = config('htmx.errors.customEventOnStatus')[$statusCode];

      $response = response()
        ->noContent()
        ->hxTrigger($customEvent);
    }

    return $response;
  }
}
