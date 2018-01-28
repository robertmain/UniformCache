<?php

namespace UniformCache\Adapters;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;

use UniformCache\Adapter;
use UniformCache\CacheItem;
use UniformCache\Exceptions\CacheException;

/**
 * Specialisation of {@link Adapter} to provide a filesystem based caching mechanism
 */
class Disk extends Adapter implements CacheItemPoolInterface
{

    /**
     * @var Array Adapter configuration
     */
    private $config;

    /**
     * @var String Fully qualified path to the cache file
     */
    private $cacheFilePath;

    /**
     * @var Array Temporary in-memory version of the cache
     */
    private $cache = [];

    /**
     * Configures the disk adapter to read and write cache data to the filesystem
     *
     * @param Array $config Configuration for the disk adapter. The `directory` and `fileName` options are both
     *                      required to configure the directory where cache files should be stored.
     *
     * @throws UniformCache\Exceptions\CacheException if the cache file is not valid JSON or unable to be
     */
    public function __construct(array $config = [])
    {
        parent::__construct();

        $this->config = $config;

        $this->cacheFilePath = $this->config['directory'] . DIRECTORY_SEPARATOR . $this->config['fileName'];

        if (!file_exists($this->config['directory'])) {
            mkdir($this->config['directory']);
        }

        if (file_exists($this->cacheFilePath)) {
            $cacheFileContents = file_get_contents($this->cacheFilePath);
            $this->cache       = json_decode($cacheFileContents, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new CacheException('Unable to parse ' . $this->cacheFilePath . ' - ' .
                                            json_last_error_msg(), json_last_error());
            }
        } else {
            touch($this->cacheFilePath);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key) : CacheItem
    {
        $cacheItem = array_filter($this->cache, function ($item) use ($key) {
            return $item['key'] == $key;
        });

        $cacheItem = array_values($cacheItem);

        if (count($cacheItem) > 0) {
            return ($this->createCacheItem)($key, $cacheItem[0]['value'], true);
        } else {
            return ($this->createCacheItem)($key, null, false);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = []) : array
    {
        return array_map(function ($key) {
            return $this->getItem($key);
        }, $keys);
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     */
    public function clear()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     */
    public function deleteItem($key)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     */
    public function deleteItems(array $keys)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     */
    public function save(CacheItemInterface $item)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     */
    public function commit()
    {
        return false;
    }
}
