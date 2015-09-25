<?php

/**
 * This file is part of Uniform Cache.
 * 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * 
 */

namespace UniformCache\adapters;
require_once('Adapter.interface.php');

/**
 * Redis Adapter
 *
 * Redis Adapter - Provides redis caching functionality to UniformCache
 * 
 * @author Tim Spiekerkoetter
 * @package UniformCache
 * @copyright Tim Spiekerkoetter
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * 
 */
class RedisAdapter implements Adapter {

	/**
	 * Redis connection
	 * @var object An instance of \Redis that can be queried for cache info
	 */
	private $connection;

	/**
	 * The settings array provided to the adapter by the adapter manager
	 * @var array
	 */
	private $settings;

	/**
	 * Constructs the redis adapter and stores the provided settings and a redis instance in private properties
	 * @param array $settings An array of adapter settings
	 */
	public function __construct($settings) {
		$this->settings = $settings;
		$this->connection = $this->connect();
	}

	/**
	 * Initialize a redis connection
	 *
	 * Connect to the redis server specified in the settings array
	 */
	private function connect() {
		$connection = new \Redis();	
		if(isset($this->settings['server']) && isset($this->settings['port'])) {
			$connection->connect($this->settings['server'], $this->settings['port']);
		}else{
			$connection->connect($this->settings['server']);
		}

		if(isset($this->settings['password'])) {
			$connection->auth($this->settings['password']);
		}

		if(isset($this->settings['database'])) {
			$connection->select($this->settings['database']);
		}
		$this->connection = $connection;
	}

	/**
	 *
	 * Retrieve an item from the cache.
	 *
	 * The third argument to this method provides the required data, in the event that this is not available in the cache already(basically, a read through cache).
	 *
	 * @param  mixed $key The key to be used to retrieve the requested data from the cache
	 * @return mixed The data stored in the cache under the key specified by `$key`
	 */
	public function get($key) {
		return $this->connection->get($key);
	}

	/**
	 *
	 * Save an item to the cache.
	 *
	 * Save an item to the cache for the time specificed by the `$ttl` parameter
	 *
	 * @param mixed $key The key used to store the cache data
	 * @param mixed $value The cache data
	 * @param int $ttl The time `$value` should be persisted in the cache for (in seconds).
	 */
	public function set($key, $value, $ttl) { 
		$this->connection->set($key, $value, $ttl);
	}

	/**
	 *
	 * Remove one item
	 *
	 * Removes an item (specified by `$key`) from the cache
	 *
	 * @param mixed $key The key of the data you wish to remove from the cache
	 */
	public function delete($key) {
		$this->connection->delete($key);
	}

	/**
	 * Purge the cache
	 *
	 * Purges the cache completely. On shared resources such as APC or shared memory - a prefix is used when adding items to the cache. This same prefix is then used to avoid collisions when purging(for some reason other users of shared server tend not to like having their cache forcibly purged by another user...)
	 */
	public function purge() {
		$this->connection->flushDB();
	}

	/**
	 * Get Adaper Priorty
	 *
	 * Returns the adapter priorty. Certain adapters take priority over others. For example, the DummyAdapter has the highest priorty(1) because it is always available. Higher priority adapters are examined first to determine if they are instanciable.
	 * @return int The priroty of the current adapter. A lower number has a higher priorty.
	 */
	public static function getPriority(){
		return 7;
	}

	/**
	 * Check If This Adapter Is Usable
	 *
	 * Check to see if the adapter is able to be instanciated. Some adapters turn this on and off based on certain conditions. For example, the MySQL adapter might check to see if MySQL is installed and/or enabled and set this to `false` if not. This would prevent UniformCache from trying to instanciate the MySQL adapter.
	 * @return  boolean Value representing whether or not the adapter can be instanciated
	 */
	public static function usable(){
		return class_exists('Redis');
	}
}
