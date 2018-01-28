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
     *
     */
}
