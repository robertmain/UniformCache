<?php

namespace UniformCache\Test\Unit;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;

use UniformCache\CacheItem;
use UniformCache\Adapters\Disk;
use UniformCache\Exceptions\CacheException;
use SebastianBergmann\CodeCoverage\Node\Directory;

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

        $this->adapter = new Disk([
            'directory' => vfsStream::url($this->file_system->path()),
            'fileName'  => 'valid.json'
        ]);
    }

    /**
     * @test
     */
    public function returns_a_cache_item_represented_by_the_specified_key()
    {
        $cacheItem = $this->adapter->getItem('my_key');

        $this->assertInstanceOf(CacheItem::class, $cacheItem);
        $this->assertTrue($cacheItem->isHit());
        $this->assertEquals('my_key', $cacheItem->getKey());
        $this->assertEquals('my_item', $cacheItem->get());
    }

    /**
     * @test
     */
    public function returns_a_cache_item_even_in_the_event_of_a_cache_miss()
    {
        $cacheItem = $this->adapter->getItem('doesntExist');

        $this->assertInstanceOf(CacheItem::class, $cacheItem);
        $this->assertFalse($cacheItem->isHit());
    }

    /**
     * @test
     */
    public function cannot_read_an_invalid_cache_file()
    {
        $this->expectException(CacheException::class);
        $this->getExpectedExceptionMessageRegExp('/Unable to parse/');

        $adapter = new Disk([
            'directory' => vfsStream::url($this->file_system->path()),
            'fileName'  => 'invalid.json'
        ]);
    }

    /**
     * @test
    */
    public function creates_a_cache_file_if_one_does_not_exist()
    {
        $testDir = vfsStream::url($this->file_system->path() . DIRECTORY_SEPARATOR . 'non-existant directory');

        $adapter = new Disk([
            'directory' => $testDir,
            'fileName' => 'auto_generated_cache_file.json'
        ]);

        $this->assertDirectoryExists($testDir);
        $this->assertFileExists($testDir . DIRECTORY_SEPARATOR . 'auto_generated_cache_file.json');
    }

}
