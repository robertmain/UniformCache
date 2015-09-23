<?php
require_once('./vendor/autoload.php');
class APCAdapterTest extends \PHPUnit_Framework_TestCase{
    private $cache;
    private $cacheKey = "cacheKey";
    private $cacheValue = "cacheValue";
    public function setUp(){
        $this->cache = new UniformCache\adapters\APCAdapter(
            array()
        );
        $this->cache->set($this->cacheKey, $this->cacheValue);
    }
    /**
    * @test
    */
    public function testGet(){
        $returnedValue = $this->cache->get($this->cacheKey);
        $this->assertEquals($this->cacheValue, $returnedValue);
    }
 
    /**
    * @test
    */
    public function testDelete(){
        $this->cache->delete($this->cacheKey);
        $this->assertEquals($this->cache->get($this->cacheKey), false);
    }
 
    /**
    * @test
    */
    public function testPurge(){
        $this->cache->set($this->cacheKey, $this->cacheValue);
        $this->cache->purge();
        $this->assertEquals($this->cache->get($this->cacheKey), false);
    }
}