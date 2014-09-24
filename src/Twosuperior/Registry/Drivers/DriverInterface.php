<?php namespace Twosuperior\Registry\Drivers;

interface DriverInterface {

    /**
     * Get value from registry
     * 
     * @param  string $key
     * @param  string $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Set value into registry
     * 
     * @param  string $key
     * @param  mixed $value
     * @return bool
     */
    public function set($key, $value);

    /**
     * Bulk store values
     * 
     * @param  string $key
     * @param  mixed $value
     * @return bool
     */
    public function store(array $values);
	
    /**
     * Overwrite existing value from registry
     * 
     * @param  string $key
     * @param  mixed $value
     * @throw Exception
     * @return bool
     */
    public function overwrite($key, $value);

    /**
     * Remove existing value from registry
     * 
     * @param  string $key
     * @throw Exception
     * @return bool
     */
    public function forget($key);

    /**
     * Clear registry
     * 
     * @param  string $key
     * @return bool
     */
    public function flush();

    /**
     * Fetch all values from a key
     * 
     * @param  string $key
     * @param  string $default
     * @return mixed
     */
    public function dump($key, $default = null);

}
