<?php

class MySQLAdapterTest extends \PHPUnit_Framework_TestCase
{
    private $cache;
    private $cacheKey   = "cacheKey";
    private $cacheValue = "cacheValue";

    public function setUp()
    {
        $this->cache = new UniformCache\adapters\MySQLAdapter(
            [
            'hostname' => 'localhost',
            'port'     => 3306,
            'username' => 'root',
            'password' => '',
            'table'    => 'cache',
            'database' => 'uniformcache'
            ]
        );
        $this->cache->set($this->cacheKey, $this->cacheValue);
    }

    /**
     * @test
     */
    public function testGet()
    {
        $returnedValue = $this->cache->get($this->cacheKey);
        $this->assertEquals($this->cacheValue, $returnedValue);
    }

    /**
     * @test
     */
    public function testDelete()
    {
        $this->cache->delete($this->cacheKey);
        $this->assertEquals($this->cache->get($this->cacheKey), false);
    }

    /**
     * @test
     */
    public function testPurge()
    {
        $this->cache->set($this->cacheKey, $this->cacheValue);
        $this->cache->purge();
        $this->assertEquals($this->cache->get($this->cacheKey), false);
    }
}
