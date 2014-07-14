<?php
namespace UniformCache;
require_once('./vendor/autoload.php');
include(dirname(__FILE__) . '/AdapterManager.php');
class Cache{
	private $adapter;
	public function __construct($settings = array()){
		$adapterManager = new AdapterManager($settings);
		$this->adapter = $adapterManager->getAdapter();
	}
	public function get($key, $generator = false){
		if(@!$this->adapter->get($key)){
			if(is_callable($generator)){
				$results = $generator();
				$this->set($key, $results['value'], $results['ttl']);
				return $this->adapter->get($key);
			}
			else{
				return false;
			}
		}
		else{
			return $this->adapter->get($key);
		}
	}
	public function set($key, $value, $ttl=0){
		$this->adapter->set($key, $value, $ttl);
	}
	public function delete($key){
		$this->adapter->delete($key);
	}
	public function purge(){
		$this->adapter->purge();
	}
}