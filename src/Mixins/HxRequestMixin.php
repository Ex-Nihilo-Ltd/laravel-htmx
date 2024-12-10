<?php

namespace Exn\LaravelHtmx\Mixins;

use Closure;
use Exn\LaravelHtmx\Constants\HxRequestConstants;
use Illuminate\Http\Request;

class HxRequestMixin
{
    public function hx(): Closure
    {
        /**
         * Checking if HX-Request header exists
         *
         * HX-Request header is always set on "true" if htmx issued the request
         *
         * @return bool
         */
        return function (): bool {
            return $this->hasHeader(HxRequestConstants::HX_REQUEST_HEADER);
        };
    }

    public function hxBoosted(): Closure
    {
        /**
         * Checking if HX-Boosted header is set to "true"
         *
         * HX-Boosted header indicates that the request is via an element using hx-boost
         *
         * @return bool
         */
        return function (): bool {
            return $this->header(HxRequestConstants::HX_BOOSTED_HEADER) == 'true';
        };
    }

    public function hxCurrentUrl(): Closure
    {
        /**
         * Returning a value of HX-Current-URL header (current url of the browser)
         *
         * @return ?string
         */
        return function (): ?string {
            return $this->header(HxRequestConstants::HX_CURRENT_URL_HEADER);
        };
    }

    public function hxTarget(): Closure
    {
        /**
         * Returning a value of HX-Target header (id of a target element if it exists)
         *
         * @return ?string
         */
        return function (): ?string {
            return $this->header(HxRequestConstants::HX_TARGET_HEADER);
        };
    }

    public function hxTrigger(): Closure
    {
        /**
         * Returning a value of HX-Trigger header (id of a trigger element if it exists)
         *
         * @return ?string
         */
        return function (): ?string {
            return $this->header(HxRequestConstants::HX_TRIGGER_HEADER);
        };
    }

    public function hxTriggerName(): Closure
    {
        /**
         * Returning a value of HX-Trigger-Name header (name of a target element if it exists)
         *
         * @return ?string
         */
        return function (): ?string {
            return $this->header(HxRequestConstants::HX_TRIGGER_NAME_HEADER);
        };
    }

    public function hxPrompt(): Closure
    {
        /**
         * Returning a value of HX-Prompt header (the user response to a hx-prompt)
         *
         * @return ?string
         */
        return function (): ?string {
            return $this->header(HxRequestConstants::HX_PROMPT_HEADER);
        };
    }

    public function hxHistoryRestoreRequest(): Closure
    {
        /**
         * Checks if HX-History-Restore-Request is set to "true" (if the request is for history restoration after a miss in the local history cache)
         *
         * @return bool
         */
        return function (): bool {
            return $this->header(HxRequestConstants::HX_HISTORY_RESTORE_REQUEST) == 'true';
        };
    }

    public function hxPartialRequest(): Closure
    {
        /**
         * Helper method to decide whether request should return back just a htmx partial or a full page
         *
         * Return value will be true if the request has HX-Request header and doesn't have any of the
         * HX-Boosted or HX-History-Restore-Request headers or if it wasn't a HX-Redirect
         * (our middleware flashes to session custom key to indicate redirection was made through htmx)
         *
         * @return bool if the request is expecting htmx partial or a full page
         */
        return function (): bool {
            return $this->hx() && ! (
                $this->hxBoosted()
                || $this->hxHistoryRestoreRequest()
                || $this->session()->has(HxRequestConstants::_HX_REDIRECTED)
            );
        };
    }

    public function hxValidationFailView(): Closure
    {
        /**
         * Helper method to handle meta-data information about the validation failing strategy
         *
         * If arguments are passed, they are set to the request for error handling middleware
         * to know what to do in case of the validation fail (render a view), and a response
         * instance will be returned back (to allow chaining)
         *
         * If no arguments are passed it will just return the value of metadata (that should be previously set)
         *
         * @param  ?string  $viewName  view name to render in case of a validation fail
         * @param  ?array  $data  additional data to send to the view in case of a validation fail
         * @return Request|string|array{view: string, data: array}|null
         */
        return function (?string $viewName = null, ?array $data = null, ?int $status = 200): Request|string|array|null {
            if ($viewName) {
                $this->merge([
                    HxRequestConstants::_HX_ON_VALIDATION_FAIL_KEY => $data == null
                        ? $viewName
                        : ['view' => $viewName, 'data' => $data, 'status' => $status],
                ]);

                return $this;
            } else {
                return $this->input(HxRequestConstants::_HX_ON_VALIDATION_FAIL_KEY, null);
            }

        };
    }
}
