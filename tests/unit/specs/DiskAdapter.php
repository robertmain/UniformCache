<?php

use PHPUnit\Framework\TestCase;

use UniformCache\Cache;
use UniformCache\CacheItem;
use UniformCache\Adapter;

/**
 * DiskAdapter
 *
 */
class DiskAdapter extends TestCase
{

    private $adapter;

    public function setUp()
    {
        $this->adapter = $this->getMockBuilder(Adapter::class)
                              ->enableOriginalConstructor()
                              ->getMockForAbstractClass();
    }

    /**
     * @test
     */
    public function returns_a_cache_item_representing_the_specified_key()
    {
        $this->adapter->method('get')
                      ->willReturnCallback(function () {
                            $cacheItemGenerator = $this->adapter->createCacheItem;
                            return [
                                $cacheItemGenerator('my_key', 'my_item', true),
                                $cacheItemGenerator('my_key_2', 'my_item_2', true)
                            ];
                      });

        $cache     = new UniformCache\Cache([$this->adapter]);
        $cacheItem = $cache->getItem('my_key');

        $this->assertInstanceOf(CacheItem::class, $cacheItem);
        $this->assertEquals('my_key', $cacheItem->getKey());
        $this->assertEquals('my_item', $cacheItem->get());
    }
}
