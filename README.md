# Laravel-htmx

Laravel package to simplify interactions with htmx on the front-end. It extends given
Laravel base classes (e.g. `Request`, `Response`) and injects additional methods (macros)
or given behavior (e.g. intercept `ValidationException` handling in case of htmx request).

Developed by [ExN Team](https://exndev.com/)

Usage

- [Request](#request)
- [Response](#response)
- [Error handling](#error-handling)
- [Middlewares](#middlewares)

**Important note** this package is still in **dev mode** and thus is not stable for production use.

## Setup

While this is in a **dev mode** you need to add a repository on the `composer.json` file that will allow `composer` to locate the package, and add it as a requirement

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/Ex-Nihilo-Ltd/laravel-htmx"
    }
  ],
  "require": {
    "exn/laravel-htmx": "dev-master"
  }
}
```

After setting that up simple `composer update` should do the trick.

## Request

`Illuminate\Http\Request` is extended through `macro` feature of Laravel framework, which allows usage
directly through the injected `$request` variable. There is also a helper class that can be imported
as `Exn\Laravel\HxRequest`, which offers all of the same methods in a static form, which would be
automatically applied onto the current request (through `request()` helper function).

Methods available on the `$request` (or on `HxRequest`)

- `hx(): bool` - Checking if HX-Request header exists
- `hxBoosted(): bool` - Checking if HX-Boosted header is set to "true"
- `hxCurrentUrl(): string|null` - Returning a value of HX-Current-URL header (current url of the browser)
- `hxTarget(): string|null` - Returning a value of HX-Target header (id of a target element if it exists)
- `hxTrigger(): string|null` - Returning a value of HX-Trigger header (id of a trigger element if it exists)
- `hxTriggerName(): string|null` - Returning a value of HX-Trigger-Name header (name of a target element if it exists)
- `hxPrompt(): string|null` - Returning a value of HX-Prompt header (the user response to an hx-prompt)
- `hxHistoryRestoreRequest(): bool` - Checks if HX-History-Restore-Request is set to "true" (if the request is for history restoration after a miss in the local history cache)
- `hxSetValidationFailView(string $viewName, ?array $data): void` - Used to set internal metadata on request to flag custom validation fail behavior (more about this in [error handling section](#error-handling))
- `hxValidationFailView(): string|array|null` - Used to read internal metadata on request related to a custom validation fail behavior (more about this in [error handling section](#error-handling))

As mentioned all of these methods can be used through `$request` variable or through a helper `HxRequest` class. For example following examples would have the same effect.

```php
use Illuminate\Http\Request;

class IndexController
{
  public function index(Request $requst)
  {
    if ($request->hx()) {
      return view('components.htmx.index');
    }

    return view('pages.index');
  }
}
```

```php
use Exn\LaravelHtmx\HxRequest;

class IndexController
{
  public function index()
  {
    if (HxRequest::hx()) {
      return view('components.htmx.index');
    }

    return view('pages.index');
  }
}
```

## Response

`Illuminate\Http\Response` is extended through `macro` feature of Laravel framework, which allows usage directly on the response object created for a response.

Methods available on the response

- `hxLocation(string $location): Response` - Sets header for HX-Location (allows you to do a client-side redirect that does not do a full page reload). Keep in mind that status returned with this header should not be 3xx but rather 2xx
- `hxRedirect(string $redirect): Response` - Sets header for HX-Redirect (can be used to do a client-side redirect to a new location)
- `hxPushUrl(string|bool $pushUrl): Response` - Sets header for HX-Push-Url (pushes a new url into the history stack)
- `hxReplaceUrl(string|bool $replaceUrl): Response` - Set header for Hx-Replace-Url (replaces the current URL in the location bar)
- `hxRefresh(?bool $refresh = true): Response` - Set header for Hx-Refresh (if set to “true” the client-side will do a full refresh of the page)
- `hxReswap(string $swap): Response` - Set header for HX-Reswap (allows you to specify how the response will be swapped. See hx-swap for possible values)
- `hxRetarget(string $target): Response` - Set header for HX-Retarget (a CSS selector that updates the target of the content update to a different element on the page)
- `hxReselect(string $select): Response` - Set header for HX-Reselect (a CSS selector that allows you to choose which part of the response is used to be swapped in. Overrides an existing hx-select on the triggering element)
- `hxTrigger(string|array $trigger): Response` - Wrapper around setting HX-Trigger header (allows you to trigger client-side events). You can format trigger(s) in PHP syntax (e.g. ['showMessage' => ['target' => '#otherElement']]) and it would be properly formatted to the HX-Trigger header.
- `hxTriggerAfterSettle(string|array $trigger): Response` - Wrapper around setting HX-Trigger-After-Settle header (allows you to trigger client-side events). You can format trigger(s) in PHP syntax (e.g. ['showMessage' => ['target' => '#otherElement']]) and it would be properly formatted to the HX-Trigger-After-Settle header.
- `hxTriggerAfterSwap(string|array $trigger): Response` - Wrapper around setting HX-Trigger-After-Swap header (allows you to trigger client-side events). You can format trigger(s) in PHP syntax (e.g. ['showMessage' => ['target' => '#otherElement']]) and it would be properly formatted to the HX-Trigger-After-Swap header.

All of the methods return back the instance of the `Illuminate\Http\Response` back mainly to allow chaining of the results. Example usage (from the error handling)

```php
// ...
response($errorView)
  ->hxTrigger(['show-notification' => 'Something went wrong'])
  ->hxRetarget('body')
  ->hxReswap('innerHTML')
  ->hxReselect(' ');
```

## Error Handling

Laravel-htmx tries to intercept default error handling methods (in case of htmx requests) to allow for seemles integration on the laravel framework and to avoid having additional "boilerplate" code added on in order to make everything work. Having said that currently parts that are modified are:

- [Validation Errors](#validation-errors)
- [Showing Errors on the Full Page](#showing-errors-on-the-full-page)
- [Showing Custom Notification on Specific Response Status](#showing-custom-notification-on-specific-response-status)

### Validation Errors

Default laravel behavior on validation fail (`$request->validate()`) is to redirect back with the errors (and old input) flashed to the session so that blade template can access them through [directives](). In case of the htmx requests, full page redirect is not needed, only the form that issued the request could be re-rendered (with errors and old input flashed in the session).

Some of the examples seen across the internet include manually catching `ValidationException` in the controller and doing something different in that case (example)

```php
class IndexController
{
  public function store(Request $request)
  {
    try {
      $validated = $request->validate([
        'message' => 'required|string|max:255',
      ]);

      // store logic

      return to_route('index');
    } catch (ValidationException $exception) {
      // could be done with $request->hx() if using exn/laravel-htmx
      if ($request->hasHeader('HX-Request')) {
        $request->flash();
        return response()
          ->view('components.htmx.form')
          ->withErrors($exception->errors());
      }

      throw $exception;
    }
  }
}
```

Or having a custom `Validator` made rather than using a default `$request->validate` and then using `$validator->fails()` method to check for fails manually rather than relying on the Laravel built in mechanisms (example)

```php
class IndexController
{
  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'message' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
      // could be done with $request->hx() if using exn/laravel-htmx
      if ($request->hasHeader('HX-Request')) {
        $request->flash();
        return response()
          ->view('components.htmx.form')
          ->withErrors($validator);
      } else {
        return redirect('/')
          ->withErrors($validator)
          ->withInput();
      }
    }

    $validated = $validator->validated();
    // store logic
    return to_route('index');
  }
}
```

Both of the above methods are too verbose and "clunky", while the whole point of using htmx with Laravel is to gain simple and effective approach to build modern day web applications.

There could be a simplification in the given examples with assumption that request handled by `IndexController::store` would only be htmx (might be good to check that as well - `Exn\Laravel\Http\Middleware\HtmxRequestOnly`), but it'd still require some boilerplate wrapping code and would also have an issue with [`FormRequest`](https://laravel.com/docs/11.x/validation#form-request-validation), since in there validation is done automatically before the controller is reached, so there is no easy way of intercepting that flow.

The solution laravel-htmx offers is a simple additional `$request` method call that would add metadata on the `$request` which would then later on be used to intercept default laravel behavior with a custom view render. You simply call `hxSetValidationFailView` method on the `$request` object (or through `Exn\LaravelHtmx\HxRequest` class) and give it validation fail view data (view name as a string and optional additional data array if needed). That view would be rendered if the validaiton fails, errors and old inputs will be automatically flashed to the session, and the status code returned back would be **422** (this was done if you need to re-target stuff on the front-end side in case of the validation error, also make sure that you've set up your htmx to allow swapping on this code - [link](https://gist.github.com/lysender/a36143c002a84ed2c166bf7567b1a913) or [link](https://htmx.org/extensions/response-targets/))

Example usage (same as previous two examples)

```php
class IndexController
{
  public function store(Request $request)
  {
    $validated = $request
      ->hxSetValidationFailView('components.htmx.form')
      ->validate([
        'message' => 'required|string|max:255',
      ]);

   // store logic

   return view('components.htmx.created', $created);
  }
}
```

or with `FormRequest`

```php
class IndexStoreRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'message' => 'required|string|max:255',
    ];
  }

  // Add this
  public function prepareForValidation(): void
  {
    Exn\LaravelHtmx\HxRequest::hxSetValidationFailView('components.htmx.form');
  }
}
```

As these two examples show, changes required to make validation work with htmx are now minimal compared to the regular Laravel behavior, which was the intended action.

### Showing Errors on the Full Page

In some cases there is a need to show an error on the full page rather than have it only switch some part of the code (usually on unexpected errors or when you'd want the application flow to be broken). That is handled through a config (`config('htmx.errors.***'`), that can be published to the main app (`php artisan vendor:publish --tag=htmx-config`) and the defaults are:

```php
// config/htmx.php

return [
  "errors" => [
    "fullPageRerenderOnStatus" => [404],
    "fullPageRerenderAboveStatus" => 499,
  ]
];
```

- `config('htmx.errors.fullPageRerenderOnStatus')` is an array of statuses which should trigger full page rerender (done through `HX-Reswap`, `HX-Retarget` and `HX-Reselect` headers) - by default it is set to `[404]`
- `config('htmx.errors.fullPageRerenderAboveStatus')` - is a number of status above which all statues should by default be rerendered on the whole page (simplification for a group of statuses) - by default it is set to `499` (all 500+ statuses would be rendered as a full page)

### Showing Custom Notification on Specific Response Status

There is also a shortcut to add custom events on a given status (usually to allow some type of notification popup by default when some response status occurs). It is again done through the configuration (same as [showing errors on the full page](#showing-errors-on-the-full-page)).

```php
// config/htmx.php

return [
  "errors" => [
    "customEventOnStatus" => [
      403 => ["show-error" => "Unauthorized"],
    ],
  ]
];
```

- `config('htmx.errors.customEventOnStatus')` - is an associative array where keys are statuses that should be mapped and the values are valid `HX-Trigger` arguments (events) - could be just a string or an object representing multiple events with data.

## Middlewares

There are two middlewares included in the package `HtmxExceptionRender` and `HtmxRequestOnly` (aliased as `htmxOnly`).

`HtmxExceptionRender` is included to the default middleware map (in `web` group) of the application, and is used internally by laravel-htmx to handle errors properly - **should not be used by users directly**

`HtmxRequestOnly` is a helper middleware that ensures given requests are actually htmx requests. This one is not a required but could be used to simplify request hanling on the requests that should always come from htmx (not allowing regular requests) to avoid potential partial views on user screen (due to the programming error) and to simplify handling function (no need to check if request is htmx or not). It has an alias automatically registered (`htmxOnly`), and it accepts additional paramter of status code that should be returned back if it is not an htmx request (by defualt it is `404` - `htmxOnly` is the same as `htmxOnly:404`).
