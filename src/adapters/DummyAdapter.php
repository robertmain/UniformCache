<?php
/**
 * This file is part of Uniform Cache.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author    Robert Main
 * @package   UniformCache
 * @copyright Robert Main
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace UniformCache\adapters;

/**
 * Dummy Adapter doesn't actually do any caching(everything is a cache miss), but it's handy if
 * your application sometimes runs in an environment that doesn't support caching for whatever reason
 */
class DummyAdapter implements Adapter
{
    /**
     * The third argument to this method provides the required data, in the event that this is not available in the
     * cache already(basically, a read through cache).
     *
     * @param  mixed $key The key to be used to retrieve the requested data from the cache
     *
     * @return boolean Boolean false is always returned since we never have any data stored to return
     */
    public function get($key)
    {
        return false;
    }

    /**
     * This method does not actually store any data passed to it. It is simply to be used as a drop in replacement in
     * environments where caching is not supported
     *
     * @param mixed $key   The cache key
     * @param mixed $value The cache data
     * @param int   $ttl   The ttl of the cache data (this actually has no effect as this adapter does not store any
     *                     data passed to it)
     *
     * @return void
     */
    public function set($key, $value, $ttl = 0)
    {
    }

    /**
     * Since no data is saved to the cache, no data can therefore be delete from the cache. However, it is perfectly
     * safe to call this method without raising any kind of exception or warning
     *
     * @param $key The cache key of the data you wish to delete
     *
     * @return void
     */
    public function delete($key)
    {
    }

    /**
     * Since no data is saved to the cache, the cache cannot be purged. However, this method can safely be called
     * without raising an exception.
     *
     * @return void
     */
    public function purge()
    {
    }

    /**
     * This is used to determine which adapter should be instanciated first. The DummyAdapter has the highest priorty
     * (1) because it is the most important and has no outside dependencies. Therefore it can be instanciated and
     * called without raising any kind of exception or warning (although no data is actually cached).
     *
     * @return int The priroty of the current adapter. A lower number has a higher priorty.
     */
    public static function getPriority()
    {
        return 1;
    }

    /**
     * Check to see if the adapter is able to be instanciated. Some adapters turn this on and off based on certain
     * conditions. For example, the MySQL adapter might check to see if MySQL is installed and/or enabled and set this
     * to `false` if not. This would prevent UniformCache from trying to instanciate the MySQL adapter. DummyCache is
     * **ALWAYS** marked as usuable as a safe fallback if any of the preferred caching mechanisms are not available.
     *
     * @return boolean Value representing whether or not the adapter can be instanciated
     */
    public static function usable()
    {
        return true;
    }
}
