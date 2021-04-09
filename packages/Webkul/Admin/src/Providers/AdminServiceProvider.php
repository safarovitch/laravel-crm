<?php

namespace Webkul\Admin\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Webkul\Admin\Exceptions\Handler;
use Webkul\Admin\Menu;
use Webkul\Admin\Bouncer;
use Webkul\Admin\Facades\Menu as MenuFacade;
use Webkul\Admin\Facades\Bouncer as BouncerFacade;
use Webkul\Admin\Http\Middleware\Bouncer as BouncerMiddleware;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        include __DIR__ . '/../Http/helpers.php';

        $this->loadRoutesFrom(__DIR__ . '/../Http/routes.php');

        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'admin');

        $this->publishes([
            __DIR__ . '/../../publishable/assets' => public_path('vendor/webkul/admin/assets'),
        ], 'public');

        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'admin');

        $this->app->bind(ExceptionHandler::class, Handler::class);

        $router->aliasMiddleware('user', BouncerMiddleware::class);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerFacades();

        $this->registerConfig();
    }

    /**
     * Register Bouncer as a singleton.
     *
     * @return void
     */
    protected function registerFacades()
    {
        $loader = AliasLoader::getInstance();

        $loader->alias('Bouncer', BouncerFacade::class);
        $loader->alias('Menu', MenuFacade::class);

        $this->app->singleton('bouncer', function () {
            return new Bouncer();
        });

        $this->app->singleton('menu', function () {
            return new Menu();
        });
    }

    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/menu.php', 'menu.admin'
        );
    }
}