<?php namespace Twosuperior\Registry;

use Illuminate\Support\ServiceProvider;

class RegistryServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('twosuperior/registry', 'twosuperior/registry');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['twosuperior.registry'] = $this->app->share(function($app)
        {
            return new RegistryManager($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('twosuperior.registry');
    }

}