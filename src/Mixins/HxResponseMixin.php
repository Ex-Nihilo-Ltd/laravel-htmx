<?php

namespace Exn\LaravelHtmx\Mixins;

use Closure;
use Exn\LaravelHtmx\Constants\HxResponseConstants;
use Illuminate\Http\Response;

class HxResponseMixin
{
    public function hxLocation(): Closure
    {
        /**
         * Sets header for HX-Location (allows you to do a client-side redirect that does not do a full page reload)
         *
         * Keep in mind that status returned with this header should not be 3xx but rather 2xx
         *
         * @param  string|array  $location  URL path to redirect or "settings" object (@see https://htmx.org/headers/hx-location/)
         * @return Response
         */
        return function (string|array $location): Response {
            /** @var Response $this */
            $this->header(
                HxResponseConstants::HX_LOCATION_HEADER,
                is_array($location) ? json_encode($location) : $location,
            );

            return $this;
        };
    }

    public function hxRedirect(): Closure
    {
        /**
         * Sets header for HX-Redirect (can be used to do a client-side redirect to a new location)
         *
         * @param  string  $redirect  URL path to redirect
         * @return Response
         */
        return function (string $redirect): Response {
            /** @var Response $this */
            $this->header(HxResponseConstants::HX_REDIRECT_HEADER, $redirect);

            return $this;
        };
    }

    public function hxPushUrl(): Closure
    {
        /**
         * Sets header for HX-Push-Url (pushes a new url into the history stack)
         *
         * @param  string|bool  $pushUrl  URL to be pushed on history stack or false to prevent pushing (default `true`)
         * @return Response
         */
        return function (string|bool|null $pushUrl = true): Response {
            /** @var Response $this */
            $this->header(
                HxResponseConstants::HX_PUSH_URL_HEADER,
                is_string($pushUrl) ? $pushUrl : json_encode($pushUrl),
            );

            return $this;
        };
    }

    public function hxReplaceUrl(): Closure
    {
        /**
         * Set header for Hx-Replace-Url (replaces the current URL in the location bar)
         *
         * @param  string|bool  $replaceUrl  URL to be replaced on history stack or false to prevent replacing (default `true`)
         * @return Response
         */
        return function (string|bool|null $replaceUrl = true): Response {
            /** @var Response $this */
            $this->header(
                HxResponseConstants::HX_REPLACE_URL_HEADER,
                is_string($replaceUrl) ? $replaceUrl : json_encode(($replaceUrl))
            );

            return $this;
        };
    }

    public function hxRefresh(): Closure
    {
        /**
         * Set header for Hx-Refresh (if set to “true” the client-side will do a full refresh of the page)
         *
         * @param  bool  $refresh  default is "true"
         * @return Response
         */
        return function (?bool $refresh = true): Response {
            /** @var Response $this */
            $this->header(HxResponseConstants::HX_REFRESH_HEADER, json_encode($refresh));

            return $this;
        };
    }

    public function hxReswap(): Closure
    {
        /**
         * Set header for HX-Reswap (allows you to specify how the response will be swapped. See hx-swap for possible values)
         *
         * @param  string  $swap  swap strategy to set (e.g. 'innerHTML', 'outerHTML' etc.)
         * @return Response
         */
        return function (string $swap): Response {
            /** @var Response $this */
            $this->header(HxResponseConstants::HX_RESWAP_HEADER, $swap);

            return $this;
        };
    }

    public function hxRetarget(): Closure
    {
        /**
         * Set header for HX-Retarget (a CSS selector that updates the target of the content update to a different element on the page)
         *
         * @param  string  $target  target selector
         * @return Response
         */
        return function (string $target): Response {
            /** @var Response $this */
            $this->header(HxResponseConstants::HX_RETARGET_HEADER, $target);

            return $this;
        };
    }

    public function hxReselect(): Closure
    {
        /**
         * Set header for HX-Reselect (a CSS selector that allows you to choose which part of the response is used to be swapped in. Overrides an existing hx-select on the triggering element)
         *
         * @param  string  $select  CSS selector
         * @return Response
         */
        return function (string $select): Response {
            /** @var Response $this */
            $this->header(HxResponseConstants::HX_RESELECT_HEADER, $select);

            return $this;
        };
    }

    public function hxTrigger(): Closure
    {
        /**
         * Wrapper around setting HX-Trigger header (allows you to trigger client-side events)
         *
         * You can format trigger(s) in PHP syntax (e.g. ['showMessage' => ['target' => '#otherElement']]) and it would
         * be properly formatted to the HX-Trigger header
         *
         * @param  string|array  $trigger  if it's an object it will be json encoded
         * @return Response
         */
        return function (string|array $trigger): Response {
            /** @var Response $this */
            $this->header(
                HxResponseConstants::HX_TRIGGER_HEADER,
                is_string($trigger) ? $trigger : json_encode($trigger),
            );

            return $this;
        };
    }

    public function hxTriggerAfterSettle(): Closure
    {
        /**
         * Wrapper around setting HX-Trigger-After-Settle header (allows you to trigger client-side events)
         *
         * You can format trigger(s) in PHP syntax (e.g. ['showMessage' => ['target' => '#otherElement']]) and it would
         * be properly formatted to the HX-Trigger-After-Settle header
         *
         * @param  string|array  $trigger  if it's an object it will be json encoded
         * @return Response
         */
        return function (string|array $trigger): Response {
            /** @var Response $this */
            $this->header(
                HxResponseConstants::HX_TRIGGER_AFTER_SETTLE_HEADER,
                is_string($trigger) ? $trigger : json_encode($trigger),
            );

            return $this;
        };
    }

    public function hxTriggerAfterSwap(): Closure
    {
        /**
         * Wrapper around setting HX-Trigger-After-Swap header (allows you to trigger client-side events)
         *
         * You can format trigger(s) in PHP syntax (e.g. ['showMessage' => ['target' => '#otherElement']]) and it would
         * be properly formatted to the HX-Trigger-After-Swap header
         *
         * @param  string|array  $trigger  if it's an object it will be json encoded
         * @return Response
         */
        return function (string|array $trigger): Response {
            /** @var Response $this */
            $this->header(
                HxResponseConstants::HX_TRIGGER_AFTER_SWAP_HEADER,
                is_string($trigger) ? $trigger : json_encode($trigger),
            );

            return $this;
        };
    }
}
