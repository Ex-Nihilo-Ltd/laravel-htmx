<?php

namespace Exn\LaravelHtmx;

use Exn\LaravelHtmx\Http\Middleware\HtmxResponseAdapter;
use Exn\LaravelHtmx\Http\Middleware\HtmxRequestOnly;
use Exn\LaravelHtmx\Mixins\HxRequestMixin;
use Exn\LaravelHtmx\Mixins\HxResponseMixin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\ServiceProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

class LaravelHtmxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/htmx.php', 'htmx');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function boot(): void
    {
        $this->setUpMiddlewares();

        $this->registerRequestMacros();
        $this->registerResponseMacros();

        $this->offerPublishing();
    }

    /**
     * @throws ReflectionException
     */
    protected function registerRequestMacros(): void
    {
        if (method_exists(Request::class, 'mixin')) {
            Request::mixin(new HxRequestMixin);
        }
    }

    /**
     * @throws ReflectionException
     */
    protected function registerResponseMacros(): void
    {
        if (method_exists(Response::class, 'mixin')) {
            Response::mixin(new HxResponseMixin);
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function setUpMiddlewares(): void
    {
        $router = $this->app->get('router');

        $router->aliasMiddleware('htmxOnly', HtmxRequestOnly::class);
        $router->prependMiddlewareToGroup('web', HtmxResponseAdapter::class);
    }

    protected function offerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        if (! function_exists('config_path')) {
            // function not available and 'publish' not relevant in Lumen
            return;
        }

        $this->publishes([
            __DIR__.'/../config/htmx.php' => config_path('htmx.php'),
        ], 'htmx-config');
    }
}
