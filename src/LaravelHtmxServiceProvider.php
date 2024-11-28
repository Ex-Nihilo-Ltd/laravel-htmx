<?php

namespace Exn\LaravelHtmx;

use Exn\LaravelHtmx\Http\Middleware\HtmxExceptionRender;
use Exn\LaravelHtmx\Http\Middleware\HtmxRequestOnly;
use Exn\LaravelHtmx\Traits\HxRequestMacros;
use Exn\LaravelHtmx\Traits\HxResponseMacros;
use Illuminate\Support\ServiceProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class LaravelHtmxServiceProvider extends ServiceProvider
{
    use HxRequestMacros, HxResponseMacros;

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/htmx.php', 'htmx');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function boot(): void
    {
        $this->setUpMiddlewares();

        $this->registerRequestMacros();
        $this->registerResponseMacros();

        $this->offerPublishing();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function setUpMiddlewares(): void
    {
        $router = $this->app->get('router');

        $router->aliasMiddleware('htmxOnly', HtmxRequestOnly::class);
        $router->pushMiddlewareToGroup('web', HtmxExceptionRender::class);
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
