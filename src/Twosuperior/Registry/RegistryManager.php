<?php namespace Twosuperior\Registry;

use Illuminate\Support\Manager;

class RegistryManager extends Manager {

    /**
     * Create an instance of Fluent driver
     * 
     * @return Twosuperior\Registry\Drivers\Fluent
     */
    protected function createFluentDriver()
    {
        return new Drivers\Fluent($this->app);
    }

    /**
     * Get registry default driver
     * 
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']->get('twosuperior/registry::default');
    }

}
