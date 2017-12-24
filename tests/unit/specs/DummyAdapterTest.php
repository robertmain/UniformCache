<?php

class DummyAdapterTest extends \PHPUnit_Framework_TestCase
{
    private $cache;
    private $cacheKey   = "cacheKey";
    private $cacheValue = "cacheValue";

    public function setUp()
    {
        $this->cache = new UniformCache\adapters\DummyAdapter([]);
        $this->cache->set($this->cacheKey, $this->cacheValue);
    }
    /**
     * @test
     */
    public function testGet()
    {
        $returnedValue = $this->cache->get($this->cacheKey);
        $this->assertEquals(false, $returnedValue);
    }
}
