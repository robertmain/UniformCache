<?php
/**
 * This file is part of Uniform Cache.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UniformCache;

/**
 * Uniform Cache
 *
 * @author    Robert Main
 * @package   UniformCache
 * @copyright Robert Main
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 */
class Cache
{

    /**
     * The cache adapter currently in use
     *
     * @var \UniformCache\Adapter
     */
    private $adapter;

    /**
     * Sets up all the specified adapters so that they can be used later on in the class
     *
     * @param array                                $adapters         An array of adapters to be passed to the relevant
     *                                                               adapter(s) as constructor arguments upon
     *                                                               instanciation
     * @param \UniformCache\adapters\AdapterManager $adapter_manager An adapter factory used to resolve and create new
     *                                                               adapter instances
     */
    public function __construct(array $adapters = array(), $adapter_manager)
    {
        if (!$adapter_manager) {
            $adapterManager = new AdapterManager($adapters);
        } else {
            $adapterManager = new $adapter_manager($adapters);
        }
        $this->adapter = $adapterManager->getAdapter();
    }

    /**
     * The third argument to this method provides the required data, in the event that this is not available in the
     * cache already(basically, a read through cache).
     *
     * @param mixed         $key       The key to be used to retrieve the requested data from the cache
     * @param callable|null $generator An optional callback function which can be used to provide the required
     *                                 data(and add it to the cache) in the event of a cache miss.
     *
     * @return mixed                    The data stored in the cache under the key specified by `$key`
     */
    public function get($key, callable $generator = null)
    {
        if (@!$this->adapter->get($key)) {
            if (is_callable($generator)) {
                $results = $generator();
                $this->set($key, $results['value'], $results['ttl']);
                return $this->adapter->get($key);
            } else {
                return false;
            }
        } else {
            return $this->adapter->get($key);
        }
    }

    /**
     * Save an item to the cache for the time specificed by the `$ttl` parameter
     *
     * @param mixed   $key   The key used to store the cache data
     * @param mixed   $value The cache data
     * @param integer $ttl   The ttl (time to live) of the cached data in seconds. When this time has elapsed, the data
     *                       will be removed from the cache. Set to 0 for no limit.
     */
    public function set($key, $value, $ttl = 0)
    {
        $this->adapter->set($key, $value, $ttl);
    }

    /**
     * Removes an item (specified by `$key`) from the cache
     *
     * @param mixed $key The key of the data you wish to remove from the cache
     */
    public function delete($key)
    {
        $this->adapter->delete($key);
    }

    /**
     * Purges the cache completely. On shared resources such as APC or shared memory - a prefix is used when adding
     * items to the cache. This same prefix is then used to avoid collisions when purging(for some reason other users
     * of shared server tend not to like having their cache forcibly purged by another user...)
     */
    public function purge()
    {
        $this->adapter->purge();
    }
}
