<?php

namespace Exn\LaravelHtmx\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HtmxRequestOnly
{
  /**
   * Allows only Htmx requests to pass, otherwise aborts request with provided status code (or 404)
   *
   * @param \Illuminate\Http\Request $request
   * @param \Closure $next
   * @param mixed $statusCode
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function handle(Request $request, Closure $next, ?int $statusCode = 404): Response
  {
    if (!$request->hx()) {
      abort($statusCode);
    }

    return $next($request);
  }
}
