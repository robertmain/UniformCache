<?php

namespace UniformCache\Test\Unit;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;

use UniformCache\CacheItem;
use UniformCache\Adapters\Disk;

/**
 * DiskAdapter
 *
 */
class DiskAdapter extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $file_system;

    /**
     * @var \org\bovigo\vfs\vfsStreamFile
     */
    private $cacheFile;

    public function setUp()
    {
        $this->file_system = vfsStream::setup('root', null, [
            'valid.json'   => '[{"key": "my_key", "value": "my_item"}]',
            'invalid.json' => '{"invalid json": 3'
        ]);
    }

    /**
     * @test
     */
    public function returns_a_cache_item_represented_by_the_specified_key()
    {
        $adapter = new Disk([
            'directory' => vfsStream::url($this->file_system->path()),
            'fileName'  => 'valid.json'
        ]);

        $cacheItem = $adapter->getItem('my_key');

        $this->assertInstanceOf(CacheItem::class, $cacheItem);
        $this->assertTrue($cacheItem->isHit());
        $this->assertEquals('my_key', $cacheItem->getKey());
        $this->assertEquals('my_item', $cacheItem->get());
    }
}
