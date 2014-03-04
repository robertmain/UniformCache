<?php
namespace UniformCache;
require_once('Adapter.interface.php');
class FileWriter{
	private $fileName;

	public function __construct($fileName){
		$this->fileName = dirname(__FILE__) . '/cache/' . $fileName . '.json';
	}

	public function write($data){
		return file_put_contents($this->fileName, $data);
	}

	public function read(){
		return file_get_contents($this->fileName);
	}

	public function fileExists(){
		return file_exists($this->fileName);
	}

	public function purge(){
		unlink($this->fileName);
	}
}

class DiskAdapter implements Adapter{
	private $fileWriter;
	private $settings;
	private $db;
	private $dirty = false;
	
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
		var_dump($this->db);
	}

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

	public function get($key){
		if(($this->db[$key]['expiresAt'] <= time()) && ($this->db[$key]['expiresAt'] != 0)){
			$this->delete($key);
		}
		try{
			return $this->db[$key]['val'];
		}
		catch(Exception $e){
			return false;
		}
	}

	public function set($key, $value, $ttl){
		if($ttl == 0){
			$expiresAt = 0;
		}
		else{
			$expiresAt = time() + $ttl;
		}
		$this->db[$key] = array('val' => $value, 'expiresAt' => $expiresAt);
		$this->dirty = true;
	}

	public function delete($key){
		unset($this->db[$key]);
		$this->dirty = true;
	}

	public function purge(){
		$this->db = array();
		$this->dirty = true;
	}

	public static function getPriority(){
		return 2;
	}

	public static function usable(){
		return true;
	}
}