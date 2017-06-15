<?php namespace Twosuperior\Registry;

use Cache;
use Exception;
use Illuminate\Database\DatabaseManager;

class Registry {

	/**
	 * Registry cache
	 *
	 * @var object
	 */
	protected $storage = null;
	
    /**
     * Application instance
     * 
     * @var Illuminate\Foundation\Application
     */
    protected $config;

    /**
     * Database
     * 
     * @var string
     */
    protected $database;

    /**
     * Constructor
     * 
     * @param Illuminate\Foundation\Application $app
     */
    public function __construct(DatabaseManager $database, $config = array())
    {
		$this->config = $config;
        $this->database = $database;
		
		// Ensure cache is set
		$this->setCache();
    }

	/**
	 * Get value from registry
	 *
	 * @param  string $key
	 * @param  string $default
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		list($baseKey, $searchKey) = $this->fetchKey($key);

		$value = $this->fetchValue($baseKey, $searchKey);

		return ( ! is_null($value) ) ? $value : $default;
	}
	
	/**
	 * Get all from registry
	 *
	 * @return mixed
	 */
	public function all()
	{
		if ( ! isset($this->storage) ) return null;

		return $this->storage;
	}

	/**
	 * Store value into registry
	 *
	 * @param  string $key
	 * @param  mixed $value
	 * @return bool
	 */
	public function set($key, $value)
	{
		list($baseKey, $searchKey) = $this->fetchKey($key);
		$registry = $this->get($baseKey);

		if ( ! is_null($registry)) return $this->overwrite($key, $value);

		if ($baseKey != $searchKey)
		{
			$object = array();
			$level = '';
			$keys = explode('.', $searchKey);

			foreach ($keys as $key)
			{
				$level .= '.'.$key;
				(trim($level, '.') == $searchKey) ? array_set($object, trim($level, '.'), $value) : array_set($object, trim($level, '.'), array());
			}

			$this->database->table($this->config['table'])->insert(array('key' => $baseKey, 'value' => json_encode($object)));

			$this->storage[$baseKey] = $object;
		}
		else
		{
			$this->database->table($this->config['table'])->insert(array('key' => $baseKey, 'value' => json_encode($value)));

			$this->storage[$baseKey] = $value;
		}

		Cache::forever($this->config['cache'], $this->storage);

		return true;
	}

	 /**
	 * Overwrite existing value from registry
	 *
	 * @param  string $key
	 * @param  mixed $value
	 * @throw Exception
	 * @return bool
	 */
	public function overwrite($key, $value)
	{
		list($baseKey, $searchKey) = $this->fetchKey($key);
		$registry = $this->get($baseKey);

		if (is_null($registry)) throw new \Exception("Item [$key] does not exists");

		if ($baseKey !=  $searchKey)
		{
			array_set($registry, $searchKey, $value);
			$this->database->table($this->config['table'])->where('key', '=', $baseKey)->update(array('value' => json_encode($registry)));

			$this->storage[$baseKey] = $registry;
		}
		else
		{
			$this->database->table($this->config['table'])->where('key', '=', $baseKey)->update(array('value' => json_encode($value)));

			$this->storage[$baseKey] = $value;
		}

		Cache::forever($this->config['cache'], $this->storage);

		return true;
	}

	 /**
	 * Store an array
	 *
	 * @param  srray $key
	 * @return bool
	 */
	public function store(array $values)
	{
		foreach ($values as $key=>$value)
		{
			$jsonValue = json_encode($value);
			$this->database->statement("INSERT INTO ? ( `key`, `value` ) VALUES ( ?, ? )
										ON DUPLICATE KEY UPDATE `key` = ?, `value` = ?",
										array($this->config['table'], $key, $jsonValue, $key, $jsonValue));

			$this->storage[$key] = $value;
		}

		Cache::forever($this->config['cache'], $this->storage);

		return true;
	}

	/**
	 * Remove existing value from registry
	 *
	 * @param  string $key
	 * @throw Exception
	 * @return bool
	 */
	public function forget($key)
	{
		list($baseKey, $searchKey) = $this->fetchKey($key);
		$registry = $this->get($baseKey);

		if (is_null($registry)) throw new \Exception("Item [$key] does not exists");

		if ($baseKey !== $searchKey)
		{
			array_forget($registry, $searchKey);
			$this->database->table($this->config['table'])->where('key', '=', $baseKey)->update(array('value' => json_encode($registry)));

			$this->storage[$baseKey] = $registry;
		}
		else
		{
			$this->database->table($this->config['table'])->where('key', '=', $baseKey)->delete();

			unset($this->storage[$baseKey]);
		}

		Cache::forever($this->config['cache'], $this->storage);

		return true;
	}

    /**
     * Fetch all values from a key
     * 
     * @param  string $key
     * @param  string $default
     * @return mixed
     */
    public function dump($key, $default = null)
    {
        list($baseKey, $searchKey) = $this->fetchKey($key);
        return ( ! is_null($this->fetchValue($baseKey))) ? $this->fetchValue($baseKey) : $default;
    }

	/**
	 * Clear registry
	 *
	 * @param  string $key
	 * @return bool
	 */
	public function clear()
	{
		// forget cache
		Cache::forget($this->config['cache']);

		// Ensure new cache is set
		$this->setCache();
	}
	
	/**
	 * Clear registry
	 *
	 * @param  string $key
	 * @return bool
	 */
	public function flush()
	{
		Cache::forget($this->config['cache']);

		$this->storage = null;

		return $this->database->table($this->config['table'])->truncate();
	}

	/**
	 * Get registry key
	 *
	 * @param  string $key
	 * @return array
	 */
	protected function fetchKey($key)
	{
		if (str_contains($key, '.'))
		{
			$keys = explode('.', $key);
			$search = array_except($keys, 0);

			return array(array_get($keys, 0), implode('.', $search));
		}

		return array($key, $key);
	}

	/**
	 * Get key value
	 *
	 * @param  string $key
	 * @param  string $searchKey
	 * @return mixed
	 */
	protected function fetchValue($key, $searchKey = null)
	{
		if ( ! isset($this->storage[$key]) ) return null;

		$object = $this->storage[$key];

		return array_get($object, $searchKey);
	}

	/**
	 * Set cache
	 *
	 * @return array
	 */
	protected function setCache()
	{
		$this->storage = Cache::rememberForever($this->config['cache'], function()
		{
			$cache = array();
			foreach($this->database->table($this->config['table'])->get() as $setting)
			{
				$cache[$setting->key] = json_decode($setting->value, true);
			}
			return $cache;
		});
	}

}
