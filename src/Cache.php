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
     * @var \UniformCache\Adapter
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
     *
     * @todo Implement this method
     */
    public function hasItem($key)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @todo Implement this method
     */
    public function clear()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @todo Implement this method
     */
    public function deleteItem($key)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @todo Implement this method
     */
    public function deleteItems(array $keys)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @todo Implement this method
     */
    public function save(CacheItemInterface $item)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @todo Implement this method
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @todo Implement this method
     */
    public function commit()
    {
        return false;
    }
}
