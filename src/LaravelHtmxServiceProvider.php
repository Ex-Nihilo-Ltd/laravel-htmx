<?php

namespace Exn\LaravelHtmx;

use Exn\LaravelHtmx\Http\Middleware\HtmxExceptionRender;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Validation\ValidationException;
use ReflectionClass;
use ReflectionMethod;
use Exn\LaravelHtmx\Http\Middleware\HtmxRequestOnly;
use Exn\LaravelHtmx\Internal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\ServiceProvider;
use Throwable;
use View;

class LaravelHtmxServiceProvider extends ServiceProvider
{
  public function register()
  {
    $this->mergeConfigFrom(__DIR__ . '/../config/htmx.php', 'htmx');
  }

  public function boot()
  {
    $this->setUpMiddlewares();


    $this->registerRequestMacros();
    $this->registerResponseMacros();

    $this->interceptErrorHandling();

    $this->offerPublishing();
  }

  protected function registerRequestMacros(): void
  {
    if (!method_exists(Request::class, 'macro')) {
      return;
    }

    $reflection = new ReflectionClass(Internal\HxRequest::class);
    $methods = $reflection->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC);

    foreach ($methods as $method) {
      $methodName = $method->getName();

      // Register macro
      Request::macro($methodName, function (...$args) use ($methodName) {
        return Internal\HxRequest::$methodName($this, ...$args);
      });
    }
  }

  protected function registerResponseMacros(): void
  {
    if (!method_exists(Response::class, 'macro')) {
      return;
    }

    $reflection = new ReflectionClass(Internal\HxResponse::class);
    $methods = $reflection->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC);

    foreach ($methods as $method) {
      $methodName = $method->getName();

      // Register macro
      Response::macro($methodName, function (...$args) use ($methodName) {
        return Internal\HxResponse::$methodName($this, ...$args);
      });
    }
  }

  protected function setUpMiddlewares()
  {
    $router = $this->app->get('router');

    $router->aliasMiddleware('htmxOnly', HtmxRequestOnly::class);
    $router->pushMiddlewareToGroup('web', HtmxExceptionRender::class);
  }

  // TODO: handle this better (outside of this class)
  protected function interceptErrorHandling(): void
  {
    /** @var \Illuminate\Foundation\Exceptions\Handler */
    $errorHandler = $this->app->get(ExceptionHandler::class);

    $errorHandler->renderable(function (Throwable $exception, Request $request, ) {
      // only handle hx requests
      if (!$request->hx()) {
        return;
      }

      if ($exception instanceof ValidationException) {
        $hxOnFail = $request->hxValidationFailView();

        if ($hxOnFail) {
          $request->flash();
          $view = View::make(
            is_string($hxOnFail) ? $hxOnFail : $hxOnFail['view'],
            is_string($hxOnFail) ? [] : $hxOnFail['data']
          )->withErrors($exception->errors(), $request->input('_error_bag', $exception->errorBag));

          return response($view, 422);
        }
      }
    });
  }

  protected function offerPublishing(): void
  {
    if (!$this->app->runningInConsole()) {
      return;
    }

    if (!function_exists('config_path')) {
      // function not available and 'publish' not relevant in Lumen
      return;
    }

    $this->publishes([
      __DIR__ . '/../config/htmx.php' => config_path('htmx.php'),
    ], 'htmx-config');
  }
}
