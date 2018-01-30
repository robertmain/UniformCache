<?php

namespace UniformCache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;

/**
 * An implementation of a PSR-6 CacheItemPoolInterface
 */
class Cache implements CacheItemPoolInterface
{
    /**
     * @var \UniformCache\Adapter A caching mechanism adapter that can be utilised to store and retrieve cache items
     */
    private $adapter;

    /**
     * Construct a new cache object.
     *
     * Provide an array of one or more extensions of {@link \UniformCache\Adapter}.
    */
    public function __construct(array $adapters = [])
    {
        $this->adapter = array_filter($adapters, function ($adapter) {
            return $adapter;
        })[0];
    }

    /**
     * {@inheritdoc}
    */
    public function getItem($key): CacheItem
    {
        return $this->getItems([$key])[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = []): array
    {
        $values = [];
        foreach ($this->adapter->get($keys) as $item) {
            $values[$item->getKey()] = $item;
        }
        return $values;
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
     */
    public function clear()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        return false;
    }
}
