<?php

/**
 * This file is part of Uniform Cache.
 *
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *
 */

namespace UniformCache\adapters;

require_once('Adapter.interface.php');
require_once('./vendor/autoload.php');

/**
 * File Writer
 *
 * Object wrapper for PHP's file file_* functions
 *
 *
 * This class provides an object based interface to PHP's file_* functions. This allows files to be written and read from in an object oriented manner with a much nicer API than simply calling PHP's native functions.
 *
 * @author Robert Main
 * @package UniformCache
 * @copyright  Robert Main
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */
class FileWriter{

	/**
	 * The name of the cache file to write data to and read data from
	 *
	 * This is used as the filename where the cache
	 * 
	 * @var string
	 */
	private $fileName;

	/**
	 * Constructor for the FileWriter class. 
	 *
	 * This constructor is responsible for creating the cache file to store the cache data
	 * 
	 * @param string $fileName
	 */
	public function __construct($fileName){
		$this->fileName = dirname(__FILE__) . '/cache/' . $fileName . '.json';
		touch($this->fileName);
	}

	/**
	 * Write data to disk
	 *
	 * Any data provided to the file specified in the constructor of this class
	 * 
	 * @param  mixed $data The data to be stored in the cache file
	 */
	public function write($data){
		return file_put_contents($this->fileName, $data);
	}

	/**
	 * Read data from disk
	 *
	 * Reads the contents of the cache file into memory and returns it
	 * 
	 * @return string The contents of the cache file
	 */
	public function read(){
		return file_get_contents($this->fileName);
	}

	/**
	 * Check for the existance of a given file
	 *
	 * Checks to see if the file provided in the constructor exists.
	 * @return boolean Boolean value to indicate the existance of the file provided in `__construct`
	 */
	public function fileExists(){
		return file_exists($this->fileName);
	}

	/**
	 * Purge the cache
	 *
	 * Deltes the cache file on disk.
	 * 
	 */
	public function purge(){
		unlink($this->fileName);
	}
}


/**
 * Disk Adapter
 *
 * Disk adapter for Uniform Cache
 *
 *
 * Disk adapter for Uniform Cache. This class allows cache data to be saved to disk as a JSON file
 *
 * @author Robert Main
 * @package UniformCache
 * @copyright  Robert Main
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */
class DiskAdapter implements Adapter{

	/**
	 * An instance of \UniformCache\adapters\FileWriter to be used for accessing the filesystem
	 * @var \UniformCache\adapters\FileWriter
	 */
	private $fileWriter;

	/**
	 * An array of adapter settings in the following format:
	 * 
	 * | Key  | Description |
	 * | ------------- | ------------- |
	 * | `filename` (optional) | The filename to use when saving cache data to disk. This defaults to `cache.json`  |
	 * @var array
	 */
	private $settings;

	/**
	 * An in-memory store of the cache data. This is read into memory from disk when the object is instanciated and then syncronized with the data stored on disk when the object is destructed.
	 * @var array
	 */
	private $db;

	/**
	 * Used to determine whether the cache requires syncronization back to disk. If the cache has been changed since it was last syncronized to disk, it is considered "dirty" and should be resyncronized. Otherwise, no syncronization is performed
	 * @var boolean
	 */
	private $dirty = false;
	

	/**
	 * Constructs the disk adapter, creates cache file and stores the provided adapter settings in a private property.
	 *
	 * Responsible for creating the cache file to be used later for the storage of data.
	 * 
	 * @param array $settings An array of adapter settings
	 */
	public function __construct($settings){
		$this->settings = $settings;
		if($this->settings['filename']){
			$fileName = $this->settings['filename'];
		}
		else{
			$fileName = "cache";
		}
		$this->fileWriter = new FileWriter($fileName);
		$fileContent = $this->fileWriter->read();
		if($fileContent == ""){
			$this->db = array();
		}
		else{
			$this->db = json_decode($fileContent, true);
		}

	}

	/**
	 * Cleans up after the adapter before garbage collection
	 *
	 * This method is responsible for writing the cache data currently stored in memory as a private property(`$db`) to disk into the cache file
	 */
	public function __destruct(){
		if($this->dirty == true){
			foreach($this->db as $key => $cacheItem){
				if(($cacheItem['expiresAt'] <= time()) && ($cacheItem['expiresAt'] != 0)){
					unset($this->db[$key]);
				}
			}
			$this->fileWriter->write(json_encode($this->db));
		}
	}

	/**
	 *
	 * Retrieve an item from the cache.
	 * 
	 * The third argument to this method provides the required data, in the event that this is not available in the cache already(basically, a read through cache).
	 * 
	 * @param  mixed $key The key to be used to retrieve the requested data from the cache
	 * @return mixed The data stored in the cache under the key specified by `$key`
	 */
	public function get($key){
		if(isset($this->db[$key])){
			if(($this->db[$key]['expiresAt'] <= time()) && ($this->db[$key]['expiresAt'] != 0)){
				$this->delete($key);
			}
			else{
				return $this->db[$key]['val'];
			}
		}
		else{
			return false;
		}
	}

	/**
	 *
	 * Save an item to the cache.
	 * 
	 * Save an item to the cache for the time specificed by the `$ttl` parameter
	 * 
	 * @param mixed $key The key used to store the cache data
	 * @param mixed $value The cache data
	 * @param int $ttl The time `$value` should be persisted in the cache for (in seconds).
	 */
	public function set($key, $value, $ttl=0){
		if($ttl != 0){
			$ttl = time() + $ttl;
		}
		$this->db[$key] = array('val' => $value, 'expiresAt' => $ttl);
		$this->dirty = true;
	}

	/**
	 *
	 * Remove one item
	 *
	 * Removes an item (specified by `$key`) from the cache
	 * 
	 * @param mixed $key The key of the data you wish to remove from the cache
	 */
	public function delete($key){
		unset($this->db[$key]);
		$this->dirty = true;
	}

	/**
	 * Purge the cache
	 *
	 * Purges the cache completely. On shared resources such as APC or shared memory - a prefix is used when adding items to the cache. This same prefix is then used to avoid collisions when purging(for some reason other users of shared servers tend not to like having their cache forcibly purged by another user...)
	 */
	public function purge(){
		$this->db = array();
		$this->dirty = true;
	}

	/**
	 * Get Adaper Priorty
	 *
	 * Returns the adapter priorty. Certain adapters take priority over others. For example, the DummyAdapter has the highest priorty(1) because it is always available. Higher priority adapters are examined first to determine if they are instanciable.
	 */
	public static function getPriority(){
		return 2;
	}

	/**
	 * Check If This Adapter Is Usable
	 *
	 * Check to see if the adapter is able to be instanciated. Some adapters turn this on and off based on certain conditions. For example, the MySQL adapter might check to see if MySQL is installed and/or enabled and set this to `false` if not. This would prevent UniformCache from trying to instanciate the MySQL adapter.
	 * @return  boolean ValueValue representing whether or not the adapter can be instanciated
	 */
	public static function usable(){
		return true;
	}
}