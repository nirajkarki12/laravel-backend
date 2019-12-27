<?php

namespace App\LeaderRegistration\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class LeaderRegistrationServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(module_path('LeaderRegistration', 'database/migrations'));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path('LeaderRegistration', 'Config/config.php') => config_path('leaderregistration.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path('LeaderRegistration', 'Config/config.php'), 'leaderregistration'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/leaderregistration');

        $sourcePath = module_path('LeaderRegistration', 'resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/leaderregistration';
        }, \Config::get('view.paths')), [$sourcePath]), 'leaderregistration');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/leaderregistration');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'leaderregistration');
        } else {
            $this->loadTranslationsFrom(module_path('LeaderRegistration', 'resources/lang'), 'leaderregistration');
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if (! app()->environment('production') && $this->app->runningInConsole()) {
            app(Factory::class)->load(module_path('LeaderRegistration', 'database/factories'));
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
