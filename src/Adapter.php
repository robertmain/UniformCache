<?php

namespace UniformCache;

use UniformCache\CacheItem;
use UniformCache\Exceptions\InvalidKeyException;

/**
 * Abstract implementation of a caching mechanism
 *
 * Specialisations of this class should implement {@link Adapter}
 */
abstract class Adapter
{
    /**
     * Create Cache Item
     *
     * Certain values in {@link CacheItem} cannot be directly set (e.g `value`). Therefore a factory method must
     * exist for correctly instanciating cache items
     *
     * @param String  $key   The unique key to assign to the cache item
     * @param Mixed   $value The desired value to be cached
     * @param Boolean $isHit Represents whether or not the the cache request was a hit
     *
     * @return CacheItem
     *
    */
    protected static function createCacheItem($key, $value, $isHit)
    {
        $cacheItemFactory = \Closure::bind(
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

        return $cacheItemFactory($key, $value, $isHit);
    }

}
