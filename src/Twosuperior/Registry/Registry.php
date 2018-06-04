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
    public function __construct(DatabaseManager $database)
    {
		// load db
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
		return (!is_null($value)) ? $value : $default;
	}
	
	/**
	 * Get all from registry
	 *
	 * @return mixed
	 */
	public function all()
	{
		// no storage
		if (!isset($this->storage)) return null;

		// else return all
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

		if (!is_null($registry)) return $this->overwrite($key, $value);

		if ($baseKey != $searchKey)
		{
			// set defaults
			$object = array();
			$level = '';
			$keys = explode('.', $searchKey);

			// loop keys
			foreach ($keys as $key)
			{
				$level .= '.'.$key;
				(trim($level, '.') == $searchKey) ? array_set($object, trim($level, '.'), $value) : array_set($object, trim($level, '.'), array());
			}
			$this->database->table(config('registry.table'))->insert(array('key' => $baseKey, 'value' => json_encode($object)));
			$this->storage[$baseKey] = $object;
		}
		else
		{
			$this->database->table(config('registry.table'))->insert(array('key' => $baseKey, 'value' => json_encode($value)));
			$this->storage[$baseKey] = $value;
		}

		// remember and return
		Cache::forever(config('registry.cache'), $this->storage);
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

		if ($baseKey != $searchKey)
		{
			array_set($registry, $searchKey, $value);
			$this->database->table(config('registry.table'))->where('key', '=', $baseKey)->update(array('value' => json_encode($registry)));
			$this->storage[$baseKey] = $registry;
		}
		else
		{
			$this->database->table(config('registry.table'))->where('key', '=', $baseKey)->update(array('value' => json_encode($value)));
			$this->storage[$baseKey] = $value;
		}

		// remmeber and return
		Cache::forever(config('registry.cache'), $this->storage);
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
		// loop values
		foreach ($values as $key => $value)
		{
			$jsonValue = json_encode($value);
			$this->database->statement("INSERT INTO ? ( `key`, `value` ) VALUES ( ?, ? ) ON DUPLICATE KEY UPDATE `key` = ?, `value` = ?", array(config('registry.table'), $key, $jsonValue, $key, $jsonValue));
			$this->storage[$key] = $value;
		}

		// remmeber and return
		Cache::forever(config('registry.cache'), $this->storage);
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
			$this->database->table(config('registry.table'))->where('key', '=', $baseKey)->update(array('value' => json_encode($registry)));
			$this->storage[$baseKey] = $registry;
		}
		else
		{
			$this->database->table(config('registry.table'))->where('key', '=', $baseKey)->delete();
			unset($this->storage[$baseKey]);
		}

		// remmeber and return
		Cache::forever(config('registry.cache'), $this->storage);
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
		Cache::forget(config('registry.cache'));

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
		Cache::forget(config('registry.cache'));
		$this->storage = null;
		return $this->database->table(config('registry.table'))->truncate();
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

		return array($key, null);
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
		if (!isset($this->storage[$key])) return null;
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
		$this->storage = Cache::rememberForever(config('registry.cache'), function()
		{
			$cache = array();
			foreach($this->database->table(config('registry.table'))->get() as $setting)
			{
				$cache[$setting->key] = json_decode($setting->value, true);
			}
			return $cache;
		});
	}

}
