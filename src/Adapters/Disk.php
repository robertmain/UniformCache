<?php

namespace UniformCache\Adapters;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;

use UniformCache\Adapter;
use UniformCache\CacheItem;
use UniformCache\Exceptions\CacheException;

/**
 * Abstraction of file-system based caching.
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
                throw new CacheException('Unable to parse ' . $this->cacheFilePath . ' - ' . json_last_error_msg(), json_last_error());
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
        })[0];

        if (count($cacheItem) > 0) {
            $value = $cacheItem['value'];
            $isHit = true;
        } else {
            $value = null;
            $isHit = false;
        }
        return ($this->createCacheItem)($key, $value, $isHit);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = []) : array
    {
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
