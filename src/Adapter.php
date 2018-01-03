<?php

namespace UniformCache;

use UniformCache\CacheItem;

abstract class Adapter
{
    public function __construct()
    {
        $this->createCacheItem = \Closure::bind(
            function ($key, $value, $isHit) {
                $item        = new CacheItem();
                $item->key   = $key;
                $item->value = $value;
                $item->isHit = $isHit;
                return $item;
            },
            null,
            CacheItem::class
        );
    }

    /**
     * Retrieve one (ore more) items from the cache backend
     *
     * @param array $item_ids An array of one or more item ids to retreive from the cache
     *
     * @return CacheItem[] An array of `CacheItem` objects for the requested values. `CacheItem` objects will still
     * be returned even in the event of a cache miss.
     */
    abstract public function get(array $item_ids):array;

    /**
     * Store an item in the cache
     *
     * @param mixed $key   The key under which `$value` should be stored
     * @param mixed $value The value to store in the cache. This should be a PHP serializeable value
     *
     * @return void
     */
    abstract public function set(mixed $key, mixed $value):void;

}
