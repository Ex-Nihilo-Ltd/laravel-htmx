<?php

namespace Exn\LaravelHtmx\Http\Middleware;

use Closure;
use Exn\LaravelHtmx\Constants\HxRequestConstants;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use View;

class HtmxResponseAdapter
{
    /**
     * The view factory implementation.
     */
    protected ViewFactory $view;

    public function __construct(ViewFactory $view)
    {
        $this->view = $view;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (SymfonyResponse)  $next
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $response = $next($request);
        if (! $request->hx() || $response->isSuccessful()) {
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

        // if not redirecting, sync session to the given request
        $this->syncSessionWithImmediateResponse($request);

        // error handling
        if ($_response = $this->handleErrorResponse($response)) {
            return $_response;
        }

        return $response;
    }

    protected function handleResponseException(Request $request, mixed $response): ?SymfonyResponse
    {
        $exception = $response->exception;
        if ($exception instanceof ValidationException) {
            $hxOnFail = $request->hxValidationFailView();

            if ($hxOnFail) {
                $view = View::make(
                    is_string($hxOnFail) ? $hxOnFail : $hxOnFail['view'],
                    is_string($hxOnFail) ? [] : $hxOnFail['data']
                );

                $this->syncSessionWithImmediateResponse($request);

                return response($view, is_array($hxOnFail) ? $hxOnFail['status'] : 200)
                    ->withHeaders($response->headers);
            }
        }

        return null;
    }

    protected function handleRedirectResponse(mixed $response): ?SymfonyResponse
    {
        if ($response->isRedirect()) {
            session()->flash(HxRequestConstants::_HX_REDIRECTED);

            return $this->responseAsFullPage(
                $response->setStatusCode(200)
                    ->hxLocation($response->getTargetUrl())
            );
        }

        return null;
    }

    protected function handleErrorResponse(mixed $response): ?SymfonyResponse
    {
        $statusCode = $response->getStatusCode();

        $handlingType = config('htmx.errors.handling');
        $eventType = config('htmx.errors.eventType');
        $statusOverride = $this->getStatusOverride($statusCode);

        if ($statusOverride) {
            $handlingType = $statusOverride['handling'] ?? $handlingType;
            $eventType = $this->mergeEventType($eventType, $statusOverride['eventType'] ?? []);
        }

        if ($handlingType == 'send:event') {
            return $this->responseEvent($response, $this->generateEvent($response, $eventType));
        } elseif ($handlingType == 'full:page') {
            return $this->responseAsFullPage($response);
        } else {
            return null;
        }
    }

    protected function responseAsFullPage(mixed $response): SymfonyResponse
    {
        return $response
            ->hxRetarget('body')
            ->hxReswap('innerHTML')
            ->hxReselect(' ');
    }

    protected function responseEvent(mixed $response, array $event): SymfonyResponse
    {
        return response()
            ->noContent()
            ->withHeaders($response->headers)
            ->hxTrigger($event);
    }

    private function syncSessionWithImmediateResponse(Request $request): void
    {
        // TODO: using laravel "internals" to achieve magic behavior (can it be avoided?)

        // move all new flashes to "old" so that they get removed after this request
        $flashed = $request->session()->pull('_flash.new');
        $request->session()->put('_flash.old', $flashed);

        // Re-share errors from session if immediate response
        $this->view->share(
            'errors', $request->session()->get('errors') ?: new ViewErrorBag
        );
    }

    /**
     * @return array{handling: ?string, eventType: ?array}|null
     */
    private function getStatusOverride(int $statusCode): ?array
    {
        $statusWildcard = $statusCode >= 500 ? '5xx' : '4xx';
        $matchedOverride = null;
        if (isset(config('htmx.errors.statusOverrides')[$statusCode])) {
            $matchedOverride = config('htmx.errors.statusOverrides')[$statusCode];
        } elseif (isset(config('htmx.errors.statusOverrides')[$statusWildcard])) {
            $matchedOverride = config('htmx.errors.statusOverrides')[$statusWildcard];
        } elseif (config('app.debug') && isset(config('htmx.errors.statusOverrides.dev')[$statusCode])) {
            $matchedOverride = config('htmx.errors.statusOverrides.dev')[$statusCode];
        } elseif (config('app.debug') && isset(config('htmx.errors.statusOverrides.dev')[$statusWildcard])) {
            $matchedOverride = config('htmx.errors.statusOverrides.dev')[$statusWildcard];
        }

        return $matchedOverride;
    }

    private function mergeEventType(array $eventType, array $eventTypeOverride): array
    {
        // copy original event
        $result = array_map(fn ($eventTypeValue) => $eventTypeValue, $eventType);

        // each override key should be merged to original (if it already exists)
        // or just assigned if it didn't exist
        foreach ($eventTypeOverride as $eventTypeKey => $eventTypeValue) {
            if (isset($result[$eventTypeKey])) {
                $result[$eventTypeKey] = $this->mergeEventType($result[$eventTypeKey], $eventTypeValue);
            } else {
                $result[$eventTypeKey] = $eventTypeValue;
            }
        }

        return $result;
    }

    private function generateEvent(mixed $response, array $eventType): array
    {
        $event = [];
        foreach ($eventType as $eventTypeKey => $eventTypeValue) {
            $event[$eventTypeKey] = $eventTypeValue;
            if ($eventTypeValue == 'response:text') {
                $event[$eventTypeKey] = $response->getStatusCode() == 419
                    ? 'Page Expired'
                    : Response::$statusTexts[$response->getStatusCode()] ?? 'Something went wrong';
            } elseif ($eventTypeValue == 'exception:message') {
                $event[$eventTypeKey] = $response->exception
                    ? $response->exception->getMessage()
                    : 'Something went wrong';
            } elseif (is_array($eventTypeValue)) {
                $event[$eventTypeKey] = $this->generateEvent($response, $eventTypeValue);
            }
        }

        return $event;
    }
}
