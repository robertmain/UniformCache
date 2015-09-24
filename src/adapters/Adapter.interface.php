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

/**
 * Adapter Interface
 *
 * The implementation of an adapter
 * 
 * @author Robert Main
 * @package UniformCache
 * @copyright  Robert Main
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * 
 */
interface Adapter{
	/**
	 *
	 * Retrieve an item from the cache.
	 * 
	 * The third argument to this method provides the required data, in the event that this is not available in the cache already(basically, a read through cache).
	 * 
	 * @param  mixed $key The key to be used to retrieve the requested data from the cache
	 * @return mixed The data stored in the cache under the key specified by `$key`
	 */
	public function get($key);

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
	public function set($key, $value, $ttl);

	/**
	 *
	 * Remove one item
	 *
	 * Removes an item (specified by `$key`) from the cache
	 * 
	 * @param mixed $key The key of the data you wish to remove from the cache
	 */
	public function delete($key);

	/**
	 * Purge the cache
	 *
	 * Purges the cache completely. On shared resources such as APC or shared memory - a prefix is used when adding items to the cache. This same prefix is then used to avoid collisions when purging(for some reason other users of shared server tend not to like having their cache forcibly purged by another user...)
	 */
	public function purge();

	/**
	 * Get Adaper Priorty
	 *
	 * Returns the adapter priorty. Certain adapters take priority over others. For example, the DummyAdapter has the highest priorty(1) because it is always available. Higher priority adapters are examined first to determine if they are instanciable.
	 */
	public static function getPriority();

	/**
	 * Check If This Adapter Is Usable
	 *
	 * Check to see if the adapter is able to be instanciated. Some adapters turn this on and off based on certain conditions. For example, the MySQL adapter might check to see if MySQL is installed and/or enabled and set this to `false` if not. This would prevent UniformCache from trying to instanciate the MySQL adapter.
	 */
	public static function usable();
}
