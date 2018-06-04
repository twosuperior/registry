<?php namespace Twosuperior\Registry;

use Illuminate\Support\ServiceProvider;
use ReflectionClass;

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
		$this->publishes([
    			$this->guessPackagePath() . '/config/registry.php' => config_path('registry.php')
		], 'config');

		// Publish your migrations
		$this->publishes([
    			$this->guessPackagePath() . '/migrations/' => database_path('/migrations')
		], 'migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
		// merge config
		$this->mergeConfigFrom(
			$this->guessPackagePath() . '/config/registry.php', 'registry'
		);
		
		// register package
		$this->app->singleton('registry', function()
		{
			// get as new object
			return new Registry($this->app['db']);
		});
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('registry');
    }

	/**
	 * Guess real path of the package.
	 *
	 * @return string
	 */
	public function guessPackagePath()
	{
		$path = (new ReflectionClass($this))->getFileName();
		return realpath(dirname($path) . '/../../');
	}
}