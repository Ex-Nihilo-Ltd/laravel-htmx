<?php

namespace Exn\LaravelHtmx\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use View;

class HtmxExceptionRender
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if (!$request->hx() || $response->isOk()) {
            return $response;
        }

        // custom exception handling
        if ($_response = $this->handleResponseException($request, $response)) {
            return $_response;
        }

        // redirection adaptation for hx
        if ($_response = $this->handleRedirectResponse($response)) {
            return $_response;
        }

        // error handling
        if ($_response = $this->handleResponseByStatusCode($request, $response)) {
            return $_response;
        }

        return $response;
    }

    protected function handleResponseException(Request $request, mixed $response)
    {
        $exception = $response->exception;
        if ($exception instanceof ValidationException) {
            $hxOnFail = $request->hxValidationFailView();

            if ($hxOnFail) {
                $request->flash();
                $view = View::make(
                    is_string($hxOnFail) ? $hxOnFail : $hxOnFail['view'],
                    is_string($hxOnFail) ? [] : $hxOnFail['data']
                )->withErrors($exception->errors(), $request->input('_error_bag', $exception->errorBag));

                return response($view);
            }
        }

        return null;
    }

    protected function handleRedirectResponse(mixed $response)
    {
        if ($response->isRedirect()) {
            return $this->responseAsFullPage(
                response($response->getContent())
                    ->hxLocation($response->getTargetUrl())
            );
        }

        return null;
    }

    protected function handleResponseByStatusCode(Request $request, mixed $response)
    {
        $statusCode = $response->getStatusCode();
        if (
            in_array($statusCode, config('htmx.errors.fullPageRerenderOnStatus', [])) ||
            (config('app.debug') && $statusCode >= 500)
        ) {
            return $this->responseAsFullPage($response);
        }

        if (array_key_exists($statusCode, config('htmx.errors.customEventOnStatus', []))) {
            $customEvent = config('htmx.errors.customEventOnStatus')[$statusCode];
            if ($customEvent["useExceptionMessage"] && $response->exception) {
                $setter = &$customEvent["event"];
                foreach ($customEvent["exceptionMessageKey"] as $key) {
                    $setter = &$setter[$key];
                }

                $setter = $response->exception->getMessage();
                unset($setter);
            }

            return response()
                ->noContent()
                ->hxTrigger($customEvent["event"]);
        }

        return null;
    }

    protected function responseAsFullPage(mixed $response): Response
    {
        return $response
            ->hxRetarget('body')
            ->hxReswap('innerHTML')
            ->hxReselect(' ');
    }
}
