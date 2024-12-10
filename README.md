# Laravel-htmx

[Laravel](https://laravel.com/) package created to simplify integration of [htmx](https://htmx.org/) in the blade
templates.

Developed by [ExN Team](https://exndev.com/)

- [Setup](#setup)
- [Core concepts](#core-concepts)
    - [Motivation](#motivation)
        - [Redirection Problems](#redirection-problems)
        - [Validation Problems](#validation-problems)
        - [Error Handling Problems](#error-handling-problems)
        - [Wild Strings and Lack of Documentation](#wild-strings-and-lack-of-documentation)
    - [Features](#features)
        - [Redirections](#redirections)
        - [Validation](#validation)
        - [Error Handling](#error-handling)
        - [Helper Methods and Documentation](#helper-methods-and-documentation)
- [API](#api)
    - [Request](#request)
        - [hx(): bool](#hx-bool)
        - [hxBoosted(): bool](#hxboosted-bool)
        - [hxCurrentUrl(): string|null](#hxcurrenturl-stringnull)
        - [hxTarget(): string|null](#hxtarget-stringnull)
        - [hxTrigger(): string|null](#hxtrigger-stringnull)
        - [hxTriggerName(): string|null](#hxtriggername-stringnull)
        - [hxPrompt(): string|null](#hxprompt-stringnull)
        - [hxHistoryRestoreRequest(): bool](#hxhistoryrestorerequest-bool)
        - [hxPartialRequest(): bool](#hxpartialrequest-bool)
        - [hxValidationFailView(?string \$viewName, ?array \$data): void](#hxvalidationfailviewstring-viewname-array-data-void)
    - [Response](#response)
        - [hxLocation(string|array \$location): Response](#hxlocationstringarray-location-response)
        - [hxRedirect(string \$redirect): Response](#hxredirectstring-redirect-response)
        - [hxPushUrl(string|bool|null \$pushUrl = true): Response](#hxpushurlstringboolnull-pushurl--true-response)
        - [hxReplaceUrl(string|bool|null \$replaceUrl = true): Response](#hxreplaceurlstringboolnull-replaceurl--true-response)
        - [hxRefresh(?bool \$refresh = true): Response](#hxrefreshbool-refresh--true-response)
        - [hxReswap(string \$swap): Response](#hxreswapstring-swap-response)
        - [hxRetarget(string \$target): Response](#hxretargetstring-target-response)
        - [hxReselect(string \$select): Response](#hxreselectstring-select-response)
        - [hxTrigger(string|array \$trigger): Response](#hxtriggerstringarray-trigger-response)
        - [hxTriggerAfterSettle(string|array \$trigger): Response](#hxtriggeraftersettlestringarray-trigger-response)
        - [hxTriggerAfterSwap(string|array \$trigger): Response](#hxtriggerafterswapstringarray-trigger-response)
        - [Response Method Chaining](#response-method-chaining)
    - [Middlewares](#middlewares)

**Important note** this package is still in **dev mode** and thus is not stable for production use.

## Setup

While this is in a **dev mode** you need to add a repository on the `composer.json` file that will allow `composer` to
locate the package, and add it as a requirement

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

## Core concepts

The main idea for creating this package was to make htmx integration with Laravel easy and comfortable for any
Laravel developer.
Even though htmx integration can be done by simply adding htmx library to the Laravel / blade project, and modifying
the back-end code to return html partials depending on the request headers being set (or not set), there were some
parts that simply didn't feel Laravel like, mainly due to the need to bypass default mechanisms and modify its
behavior in specific cases.

This package was written in a way to try and preserve Laravel "magic-like" behavior, by injecting features into
the existing Laravel classes (no overrides, no re-mapping, etc.) and automatically attaching a custom middleware class
to the default `'web'` group, that analyses generated response, and modifies it if necessary to work well with htmx.

If you have any issues with responses (features like redirection adaptation don't work) make sure that you haven't
modified `'web'` middleware group in a way that would disable attaching of
`Exn\LaravelHtmx\Http\Middlewares\HtmxResponseAdapter` middleware.

Firstly, let's look at the problematic parts of the integration that were used as a motivation for this package.

### Motivation

In the following section, we'll explain a couple of common issues found when trying to implement htmx into the Laravel
application.
Each section will contain the base problem description and some solution attempts.

If you're not interested in this part, you can skip to the [Features section](#features) where the actual features
of the package are discussed, rather than the abstract problems.

#### Redirection Problems

Laravel provides pretty versatile redirection handling toolbox, but unfortunately each of them will cause full
client reload, since htmx won't handle 3xx status codes due to the browser native interception - here is the passage
from [their documentation](https://htmx.org/docs/#response-headers) mentioning that:

> Also the response headers above are not provided to htmx for processing with 3xx Redirect response codes like HTTP
> 302 (Redirect). Instead, the browser will intercept the redirection internally and return the headers and response
> from
> the redirected URL. Where possible use alternative response codes like 200 to allow returning of these response
> headers.

This means that to use htmx fully, one must completely ignore all the default redirection mechanisms and
fall back to the manual setting of specific htmx headers to achieve the same behavior through the htmx.

So instead of simple `redirect` helper function

```php
// without htmx
return redirect('/');
```

It'd look something like this

```php
// with htmx assuming that request was boosted and will target the body properly
return response(null, 200)->header('Hx-Location', '/');

// with htmx assuming that request had its own target
return response(null, 200)
    ->header('HX-Location', '/')
    ->header('HX-Retarget', 'body')
```

Even on this simple example, it's clear that it's not looking good, and is starting to pollute the code base, and what
would be the case in a bit more complex redirects like

```php
return redirect()->intended(route('route.name'))->with('status', 'Profile updated');
```

In this case, you'd firstly have to figure out how does `inteded` and `with` methods on the `RedirectResponse` actually
work (which would mean going through the source code), and then do it yourself again, just with different response
returned at the end (quick spoiler, `with` method won't work properly with HX headers by default).
Also, a thing to think about is how this would affect any "generated" or "library" parts that are using `redirect`
helpers.

#### Validation Problems

If you think about how does the Laravel validation mechanism work *"under the hood"*, it does simple redirection back
with errors *"flashed"* into the session which blade template picks up through globally accessible `$errors` variable,
as can be seen in the Laravel default error handler code:

```php
// Illuminate\Foundation\Exceptions\Handler::invalid
return redirect($exception->redirectTo ?? url()->previous())
            ->withInput(Arr::except($request->input(), $this->dontFlash))
            ->withErrors($exception->errors(), $request->input('_error_bag', $exception->errorBag));
```

Again, this won't work because redirection will cause full page reload on the client side (which is presumably one
of the reasons why you'd chosen htmx), with additional issues in this case where returning a htmx like redirect
(as shown in a previous section) will lack some of the used methods available on the `RedirectResponse` and it'd
need to be "hacked around".

One approach in resolving this would be to catch `ValidationException` yourself and handle it differently, an example
of that would be something like:

```php
try {
    $validated = $request->validate([
        'message' => 'required|string|max:255',
    ]);
    
    // rest of the "happy-path" logic    
} catch (ValidationException $e) {
      if ($request->hasHeader('HX-Request')) {
          $request->flash(); // there is no 'dontFlash' logic here
          return response()
              ->view('components.htmx.form')
              ->withErrors($exception->errors()); // there is no custom '_error_bag' logic here
      }

      throw $exception;
}
```

This is too cumbersome (especially in comparison with a simple call to `$request->validate()` method without worrying
about the "sad path" because Laravel will handle that out of the box), and not to mention not fully compatible with
all the cases covered in the default error handling (marked with the comments on the snippet).

Another approach would be to make a custom `Illuminate\Validation\Validator` and then check it's state rather
than relying on the default validator injected on the `Illuminate\Http\Request` which would look something like this:

```php
$validator = Validator::make($request->all(), [
    'message' => 'required|string|max:255',
]);

if ($validator->fails()) {
    if ($request->hasHeader('HX-Request')) {
        $request->flash(); // same issue with 'dontFlash' missing
        return response()
            ->view('components.htmx.form')
            ->withErrors($validator); // same issue with '_error_bag' missing
    } 
    
    // fall back to default behavior
    throw $validator->getException();
}

    $validated = $validator->validated();
    // store logic
}
```

Basically this approach has the same problems as the first one (cumbersome, missing specific bits of the logic etc.),
which means it's not really a better solution, just a different flavor of the same medicine Both of these two
approaches also create a "boilerplate" code that should be copy/pasted to all the controllers that do some
kind of validation (probably a lot of them).

Separate concern are `FormRequest`s which is automatically "evaluated" and handled by default Laravel processes, which
makes it harder to bypass directly in the controller.

Some of these problems could be resolved by extracting this type of logic into a global "error handler" or an extension
of a `Illuminate\Validation\Validator` class to centralize this logic into one place and by simply copy/pasting base
error handling code from the `Illuminate\Foundation\Exceptions\Handler` class (to get the full functionality), or
on some third way.
In any case, it requires quite some research, testing out a solution in different scenarios and most importantly,
maintenance of that part of the code by the original dev team (making sure it still works after some Laravel update).

#### Error Handling Problems

Error handling as such is a big part of any user facing application, handling "sad paths" and unexpected errors can,
however, become pretty "complex" in general, and adding htmx to the mix just adds to it.
We've already touched "sad path" part a bit part of the error handling process
(through [Validation Problems section](#validation-problems)), so the main focus here will be on the "unexpected"
(or more accurately "not validation") errors.

Imagine that user clicks on a button to invoke some action, and the server decides that given user is not authorized
to execute the requested action, or any other type of error occurs (e.g. user error, like resource not found, "page
expired" (419), or server error, like internal server error or service not available), except validation and
unauthorized (which are kind ofa special cases), how should that be handled?

Presumably user should see some type of indication that error has happened, and either be able to continue using the
page, or forced to go "back" or "reload the page".
How would we do this with htmx, when each request already has its own target, swap strategy, potentially a selector as
well?
Most likely with some type of response modification through htmx headers and probably status changes.

So for example, showing just a notification about the error without changing anything else on the page would probably
look something like this:

```php
// 204 by default won't cause any swap on the target by htmx (unless configured differently)
return response(null, 204) 
    ->header('HX-Trigger', '{"show-notification":{"message":"'. $exception->getMessage() .'","type":"error"}}');

// JSON for HX-Trigger can also be done through json_encode() function, which would be a bit cleaner in this case
// e.g. json_encode(['show-notification' => ['message' => $exception->getMessage(), 'type' => 'error']])
```

Keep in mind that in order for this code snippet to work properly, there must be an appropriate front-end handling of
this event.

Or in case you'd want a user to be shown just an error page (forcing him to go "back" or "reload the page"), it'd be
something like this:

```php
return response($errorPageView)
    ->header('HX-Retarget', 'body') // re-target to body
    ->header('HX-Reswap', 'innerHTML') // body cannot be swapped with outerHTML
    ->header('HX-Reselect', ' '); // reset 'select' attribute just in case
```

Neither case again looks very promising, especially if you consider how many places a different type of errors
should be handled in one way or another.
As well as it not covering any actually "unexpected" issues, where some of the nested functions would throw some type of
Exception.

Again, this can be handled with a more "global" approach, similar to the
[Validation Problems section](#validation-problems) handling suggestions, but it would be with the same type of issues
as above-mentioned.

#### Wild Strings and Lack of Documentation

The last issue that'll be mentioned here is not as bad as the previous ones.
However, it still can be annoying when working on a project with Laravel and htmx, and that is having to manually
read and parse request headers and write response headers usually by working with inline strings
(e.g. `if ($request->hasHeader('HX-History-Restore-Request') { /* ... */ }` or
`response($view)->header('HX-Retarget', 'body')`).
Having to do so might lead to a constant need for switching out of the IDE into the htmx documentation page to check
which exact string / format should be used or searching for a compatible part of the code to copy/paste in place.
Also, worth mentioning, it is fairly error-prone, due to typos that can be fairly hard to catch.

### Features

Problems mentioned in the previous section were a part of the reason why this library was created in the first place.
Even though all of these problems could be tackled on the project to project basis, it still seems that a unified
solution would provide generally better and more stable API and more consistency throughout different projects.

One of the main goals while writing this package was to keep Laravel simplicity as much as possible while adding full
support for htmx interactions.
All the features provided by this package are automatically available for usage after installation, and in some cases,
developers don't even need to think about it at all, it just works.

Similarly to the [Motivation section](#motivation) we'll divide main features provided by the package into sections.

#### Redirections

All application redirections will be automatically transferred to the htmx understandable redirections if the
request was originated by the htmx.
This allows developers to keep the simple approach in the controller logic if there is a need for redirection,
use one of the `redirect` helpers from Laravel, and it will work with htmx requests as well.

Under the hood, package catches any redirect response and converts it to a simple empty response with appropriate
response headers to indicate to the htmx that a new request should be issued and should be used as a full page response.

For example, code like this is pretty common to find on projects

```php
return redirect()->route('login');
```

Without any modification this would create a response with a status code `302` and `Location` header set to the url of
`login` route.
This package will recognize this type of response and modify it (only if the request was originated with htmx) to have
a status code of `200` and will set `HX-Location` header to the url of `login` route, along with `HX-Retarget`
(to `'body'`), `HX-Reswap` (to `'innerHTML'`) and `HX-Reselect` (to `' '`) to make sure that new response is handled
as a full page rather than using defined `hx-target`, `hx-swap` and `hx-select` for the initial request.

Additionally, any issues that would occur with this type of approach when working with sessions are also considered
and handled in the same workflow, so any usage of `->with(/* ... */)` or `->withErrors(/* ... */)` (or any other
`with*()` method) on the controller side, as well as the usage of `old()` and `$errors` helpers on the blade
templates will just work out of the box.

In conclusion, this allows developers to use redirections as they would in regular blade applications and expect it to
just work with htmx properly.

#### Validation

Laravel default validation will in case of failure redirect user "back" with errors (and old inputs) flashed into the
session.
So just due to the redirection support, the default Laravel validation mechanism will work as long as you ensure that
the original page will be rendered on "back" even if it was issued by htmx.
If there is some partial render on the same endpoint in case of htmx requests, this might become a bit problematic
because that redirection will be executed through htmx, so it might need some additional tuning.

However, there is another way provided to handle validation failure with a bit more control.
If you'd want to simply return a partial view as a result in case of failure, rather than *redirecting* the user back,
it can be done by using [hxValidationFailView](#hxvalidationfailviewstring-viewname-array-data-void) method on the
`Illuminate\Http\Request`, with view information in case of failure.

```php
$validated = $request
    ->hxValidationFailView('components.issuing-form')
    ->validate([
        /* rules */
    ]);
```

If this was set before validation, in case of validation failure, given view would be returned to the user with
status code `200`.
Validation errors and old inputs will be available through `$errors` and `old` helpers on that given blade component
automatically, and if there is any additional data that needs to be passed to the component, it can be added as a
second parameter to the `hxValidationFailView` function.
Similarly, if different status code is required on the validation fail, it can be passed as a third argument.

Keep in mind that in order for this to work properly, an issuing element must expect that result will replace form
(usually by setting `hx-target` to the form that is being submitted or some inherent wrapper).

When using `Illuminate\Foundation\Http\FormRequest` the concept remains the same, it's just necessary to at some point
(before actual validation) `hxValidationFailView` function is called.
A good place to do so is inside the
[prepareForValidation](https://medium.com/@aiman.asfia/a-guide-to-understanding-and-utilizing-prepareforvalidation-in-laravel-926f156650d5)
method.

```php
public function prepareForValidation(): void
{
    request()->hxValidationFailView('components.auth.login-form');
}
```

Note that `hxValidationFailView` is called on the `request()` helper, rather than on `$this`, which is important
distinction.
That is done because of how the Laravel internal mechanisms work in regard to `FormRequest` initialization, in which
modifying `$this` on `FormRequest` won't modify the original `Request` (which is necessary for `hxValidationFailView`
logic to work), so it's bypassed by using current request helper (`request()`) to modify the actual `Request`.

#### Error Handling

Error handling is set up to work mainly through the configuration, and basically offers two options, dispatching a
custom event or rendering response as a full page.
It is (similar to the other features) set up automatically and is deciding what to do based on the configuration
(that can be exported into the project and modified - `php artisan vendor:publish --tag=htmx-config`).

Main key in configuration affecting error handling is `'errors'`, and it consists of three main keys:

- `handling` - can be either `'send:event'` (default) or `'full:page'`
- `eventType` - describes event that will be dispatched, can be formatted in any array form.
  When actually being dispatched, this array will be crawled recursively and all recognized value "keywords" will be
  replaced with the actual values, while others will be considered hard-coded values and be just used as is.
  Recognized keywords that could be used as the value under some key are `'response:text'` and `'exception:message'`.
- `statusOverrides` - provides an option to override any type of property based on the response status.
  Keys represent actual response status that should be overwritten (e.g., `404`, `503`) or a wildcard (e.g., `'4xx'`),
  and the value is basically a partial of an `errors` key (`handling`, `eventType`), where `handling` key will
  override the default handling, but `eventType` would be recursively merged with the default `eventType` (so it can
  simply extend or replace full or partial parts of the `event`).
  There is also a special key `dev`, that has the same format as `statusOverrides` and is applied (merged in) only in
  development mode.

When the response status is above (or equal to) `400`, and request initiator was htmx, error handler will try to map
the response status to the strategy based on the config.

In case of mapped handling type being `'full:page'`, response returned will have set up headers
`HX-Retarget` (to `'body'`), `HX-Reswap` (to `'innerHTML'`) and `HX-Reselect` (to `' '`), while everything else will
remain as is.

In case of mapped handling type being `'send:event'`, response returned will be empty with a status code `204`
(indicating that nothing should be replaced on the page), and hydrated event (based on `eventType` merged from mapped
status override and default settings) will be added (in a JSON form) to the `HX-Trigger` header.
Again, in order for this to work, there must be some type of handling of a given event on the front-end side (JS).

#### Helper Methods and Documentation

This is a smaller feature, but it is still helpful when working with htmx, most of the interactions with the actual
htmx stuff is wrapped with functions and constants.
So for example, checking if request has `HX-Boosted` header is just calling the function on the current request
[hxBoosted()](#hxboosted-bool) which returns `boolean`.
In the same manner setting appropriate headers on the response object is done through methods on the
`Illuminate\Http\Response`, with an addition of not having to think about the string representation of the value being
set.
For example, some headers can contain JSON stringified strings as values, which are set through native PHP
arrays (e.g., `response()->hxTrigger(['show-notification' => ['type'=> 'error', 'message' => $e->getMessage()]])`).

All request and response HX headers are abstracted through the appropriate functions.
The full list of available methods can be found in the [API section](#api).

Each method is also documented properly with PHPDocs, so after installing the package be sure to re-run
`ide-helper:generate` command to get all documentation linked in properly into appropriate objects.

There are a couple more methods that were added for convenience.
For example, [hxPartialRequest](#hxpartialrequest-bool)
method attached to the `Illuminate\Http\Request` is a simple helper that determines if you should return a htmx partial
or a full page, it combines values of `HX-Request`, `HX-Boosted`, `HX-History-Restore-Request` headers and a specific
flag set to the state in case of htmx redirection (to know if the request was made due to the `HX-Location` header).

## API

### Request

Once package is installed, it automatically registers helper methods on the `Illuminate\Http\Request`, through
`macro` feature of the Laravel framework, which allows usage directly through the injected `$request` variable or result
of
a `request()` helper.

Methods that become available on the `$request` (or `request()`)

#### hx(): bool

Checking if HX-Request header exists. HX-Request header is always set on "true" if htmx issued the request

Example usage:

```php
if ($request->hx()) {
    // htmx issued this request
}
```

#### hxBoosted(): bool

Checking if HX-Boosted header is set to "true".
HX-Boosted header indicates that the request is via an element using hx-boost

Example usage:

```php
if ($request->hxBoosted()) {
    // request was issued with 'boosted' flag (usually through hx-boost="true" link)
}
```

#### hxCurrentUrl(): string|null

Returning a value of HX-Current-URL header (current url of the browser)

Example usage:

```php
$currentUrl = $request->hxCurrentUrl();
```

#### hxTarget(): string|null

Returning a value of HX-Target header (id of a target element if it exists)

Example usage:

```php
$target = $request->hxTarget();
```

#### hxTrigger(): string|null

Returning a value of HX-Trigger header (id of a trigger element if it exists)

Example usage:

```php
$trigger = $request->hxTrigger();
```

#### hxTriggerName(): string|null

Returning a value of HX-Trigger-Name header (name of a target element if it exists)

Example usage:

```php
$triggerName = $request->hxTriggerName();
```

#### hxPrompt(): string|null

Returning a value of HX-Prompt header (the user response to a hx-prompt)

Example usage:

```php
$prompt = $request->hxPrompt();
```

#### hxHistoryRestoreRequest(): bool

Checks if HX-History-Restore-Request is set to "true" (if the request is for history restoration after a miss in the
local history cache)

Example usage:

```php
if ($request->hxHistoryRestoreRequest()) {
    // request was made after the history cache miss
}
```

#### hxPartialRequest(): bool

Helper method to decide whether request should return just a htmx partial or a full
page.
Return value will be true if the request has HX-Request header and doesn't have any of
the HX-Boosted or HX-History-Restore-Request headers or if it wasn't a HX-Redirect (our middleware flashes to a session
custom key to indicate redirection was made through htmx)

Example usage:

```php
if ($request->hxPartialRequest()) {
    // partial htmx response is expected
} else {
    // full page response is expected
}
```

#### hxValidationFailView(string \$viewName, ?array \$data): void

Helper method to handle meta-data information about
the validation failing strategy.
If arguments are passed, they are set to the request for error handling middleware to know what to do in case of
the validation fail (render a view) (more about this in [error handling section](#error-handling)), and a response
instance will be returned (to allow chaining).
If no arguments are passed, it will just return the value of
metadata (that should be previously set)

Example usage:

```php
// in case of validation failure returned response will have status 200
// and would render "components.forms.auth" template with set `$errors`
// and "old" data, along with "$static_data" that was manually added
$validated = $request
    ->hxValidationFailView('components.forms.auth', ['static_data' => $staticData])
    ->validate([
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);
```

### Response

Once package is installed, it automatically registers helper methods on the `Illuminate\Http\Response`, through
`macro` feature of the Laravel framework, which allows usage directly through the created response.

Methods that become available on the created response

#### hxLocation(string|array $location): Response

Sets header for HX-Location (allows you to do a client-side redirect that does not do a full page reload).
Keep in mind that status returned with this header should not be 3xx but rather 2xx

Example usage:

```php
// simple location setting
response()->hxLocation('/test');

// or in the case of more fine-grained location settings
response()->hxLocation(['path' => '/test', 'target' => '#div-to-swap']);
```

#### hxRedirect(string $redirect): Response

Sets header for HX-Redirect (can be used to do a client-side full-page redirect to a new location)

Example usage:

```php
response()->hxRedirect('/test');
```

#### hxPushUrl(string|bool|null $pushUrl = true): Response

Sets header for HX-Push-Url (pushes a new url into the history stack)

Example usage:

```php
// push current url
response()->hxPushUrl(/* true */);

// push custom url
response()->hxPushUrl('/new-url');
```

#### hxReplaceUrl(string|bool|null $replaceUrl = true): Response

Set header for Hx-Replace-Url (replace the current URL in the location bar)

Example usage:

```php
// replace with current url
response()->hxReplaceUrl(/* true */);

// replace with custom url
response()->hxReplaceUrl('/new-url');
```

#### hxRefresh(?bool $refresh = true): Response

Set header for Hx-Refresh (if set to “true”, the client-side will do a full refresh of the page)

Example usage:

```php
response()->hxRefresh();
```

#### hxReswap(string $swap): Response

Set header for HX-Reswap (allows you to specify how the response will be swapped. See hx-swap for possible values)

Example usage:

```php
response()->hxReswap('innerHTML');
```

#### hxRetarget(string $target): Response

Set header for HX-Retarget (a CSS selector that updates the target of the content update to a different element on the
page)

Example usage:

```php
response()->hxRetarget('body');
```

#### hxReselect(string $select): Response

Set header for HX-Reselect (a CSS selector that allows you to choose which part of the response is used to be swapped
in. Overrides an existing hx-select on the triggering element)

Example usage:

```php
response()->hxReselect('main#content');
```

#### hxTrigger(string|array $trigger): Response

Wrapper around setting HX-Trigger header (allows you to trigger client-side events).
You can format trigger(s) in PHP syntax (e.g. `['showMessage' => ['target' => '#otherElement']]`) and it would be
properly formatted to the HX-Trigger header.

Example usage:

```php
// complex, or multiple events
response()->hxTrigger([
    'show-notification' => [
        'message' => 'This is a notification example',
        'type' => 'info'
    ],
    'simple-event',
]);

// simple event
// response()->hxTrigger('simple-event');
```

#### hxTriggerAfterSettle(string|array $trigger): Response

Wrapper around setting HX-Trigger-After-Settle header (allows you to trigger client-side events).
You can format trigger(s) in PHP syntax (e.g. `['showMessage' => ['target' => '#otherElement']]`) and it would be
properly formatted to the HX-Trigger-After-Settle header.

Example usage:

```php
// complex, or multiple events
response()->hxTriggerAfterSettle([
    'show-notification' => [
        'message' => 'This is a notification example',
        'type' => 'info'
    ],
    'simple-event',
]);

// simple event
// response()->hxTrigger('simple-event');
```

#### hxTriggerAfterSwap(string|array $trigger): Response

Wrapper around setting HX-Trigger-After-Swap header (allows you to trigger client-side events).
You can format trigger(s) in PHP syntax (e.g. `['showMessage' => ['target' => '#otherElement']]`) and it would be
properly formatted to the HX-Trigger-After-Swap header.

Example usage:

```php
// complex, or multiple events
response()->hxTriggerAfterSwap([
    'show-notification' => [
        'message' => 'This is a notification example',
        'type' => 'info'
    ],
    'simple-event',
]);

// simple event
// response()->hxTrigger('simple-event');
```

#### Response method chaining

All the methods return the instance of the `Illuminate\Http\Response` back mainly to allow chaining of the results.

Example usage (from the error handling):

```php
// ...
response($errorView)
  ->hxRetarget('body')
  ->hxReswap('innerHTML')
  ->hxReselect(' ');
```

### Middlewares

There are two middlewares included in the package `HtmxExceptionRender` and `HtmxRequestOnly` (aliased as `htmxOnly`).

`HtmxResponseAdapter` is included to the default middleware map (in `web` group) of the application, and is used
internally by laravel-htmx to handle errors properly - **should not be used by users directly**

`HtmxRequestOnly` is a helper middleware that ensures given requests are actually htmx requests.
This one is not required but could be used to simplify request handling on the requests that should always come
from htmx (not allowing regular requests) to avoid potential partial views on user screen (due to the programming error)
and to simplify handling function (no need to check if request is htmx or not).
It has an alias automatically registered (`htmxOnly`), and it accepts additional parameter of status code that should
be returned if it is not a htmx request (by default, it is `404` - `htmxOnly` is the same as `htmxOnly:404`).
