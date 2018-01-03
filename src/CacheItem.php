<?php

namespace UniformCache;

use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface
{
    protected $key;
    protected $value;
    protected $isHit;

    /**
     * {@inheritdoc}
    */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
    */
    public function get()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
    */
    public function isHit()
    {
        return $this->isHit;
    }

    /**
     * {@inheritdoc}
    */
    public function set($value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     *
     * @todo Implement this method
    */
    public function expiresAt($expiration)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @todo Implement this method
    */
    public function expiresAfter($time)
    {
    }
}
