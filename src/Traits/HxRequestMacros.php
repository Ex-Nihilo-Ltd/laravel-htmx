<?php

namespace Exn\LaravelHtmx\Traits;

use Exn\LaravelHtmx\Constants\HxRequestConstants;
use Illuminate\Http\Request;

trait HxRequestMacros
{
    protected function registerRequestMacros(): void
    {
        if (! method_exists(Request::class, 'macro')) {
            return;
        }

        /**
         * Checking if HX-Request header exists
         *
         * HX-Request header is always set on "true" if htmx issued the request
         *
         * @return bool
         */
        Request::macro('hx', function (): bool {
            return $this->hasHeader(HxRequestConstants::HX_REQUEST_HEADER);
        });

        /**
         * Checking if HX-Boosted header is set to "true"
         *
         * HX-Boosted header indicates that the request is via an element using hx-boost
         *
         * @return bool
         */
        Request::macro('hxBoosted', function (): bool {
            return $this->header(HxRequestConstants::HX_BOOSTED_HEADER) == 'true';
        });

        /**
         * Returning a value of HX-Current-URL header (current url of the browser)
         *
         * @return ?string
         */
        Request::macro('hxCurrentUrl', function (): ?string {
            return $this->header(HxRequestConstants::HX_CURRENT_URL_HEADER);
        });

        /**
         * Returning a value of HX-Target header (id of a target element if it exists)
         *
         * @return ?string
         */
        Request::macro('hxTarget', function (): ?string {
            return $this->header(HxRequestConstants::HX_TARGET_HEADER);
        });

        /**
         * Returning a value of HX-Trigger header (id of a trigger element if it exists)
         *
         * @return ?string
         */
        Request::macro('hxTrigger', function (): ?string {
            return $this->header(HxRequestConstants::HX_TRIGGER_HEADER);
        });

        /**
         * Returning a value of HX-Trigger-Name header (name of a target element if it exists)
         *
         * @return ?string
         */
        Request::macro('hxTriggerName', function (): ?string {
            return $this->header(HxRequestConstants::HX_TRIGGER_NAME_HEADER);
        });

        /**
         * Returning a value of HX-Prompt header (the user response to a hx-prompt)
         *
         * @return ?string
         */
        Request::macro('hxPrompt', function (): ?string {
            return $this->header(HxRequestConstants::HX_PROMPT_HEADER);
        });

        /**
         * Checks if HX-History-Restore-Request is set to "true" (if the request is for history restoration after a miss in the local history cache)
         *
         * @return bool
         */
        Request::macro('hxHistoryRestoreRequest', function (): bool {
            return $this->header(HxRequestConstants::HX_HISTORY_RESTORE_REQUEST) == 'true';
        });

        /**
         * Helper method to set meta-data information about the validation failing strategy
         *
         * It instructs validation error handling to render given view (with optional data)
         * rather than using standard validation fail strategy (redirect back)
         *
         * @param  string  $viewName  view name to render in case of a validation fail
         * @param  ?array  $data  additional data to send to the view in case of a validation fail
         * @return Request
         */
        Request::macro('hxSetValidationFailView', function (string $viewName, ?array $data = null): Request {
            $this->merge([
                HxRequestConstants::_HX_ON_VALIDATION_FAIL_KEY => $data == null
                    ? $viewName
                    : ['view' => $viewName, 'data' => $data],
            ]);

            return $this;
        });

        /**
         * Helper method to extract meta-data information about the validation failing strategy
         *
         * @return string|array{view: string, data: array} either a view name or an array containing both view name and additional data that should be passed
         */
        Request::macro('hxValidationFailView', function (): string|array|null {
            return $this->input(HxRequestConstants::_HX_ON_VALIDATION_FAIL_KEY, null);
        });

        /**
         * Helper method to decide whether request should return back just a htmx partial or a full page
         *
         * Return value will be true if the request has HX-Request header and doesn't have any of the
         * HX-Boosted or HX-History-Restore-Request headers or if it wasn't a HX-Redirect
         * (our middleware flashes to session custom key to indicate redirection was made through htmx)
         *
         * @return bool if the request is expecting htmx partial or a full page
         */
        Request::macro('hxPartialRequest', function (): bool {
            return $this->hx() && ! (
                $this->hxBoosted()
                || $this->hxHistoryRestoreRequest()
                || $this->session()->has(HxRequestConstants::_HX_REDIRECTED)
            );
        });
    }
}
