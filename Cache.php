<?php
namespace UniformCache;
include(dirname(__FILE__) . '/AdapterManager.php');
class Cache{
	private $adapter;
	private $settings;
	public function __construct($settings = array()){
		$this->settings = $settings;
		$adapterManager = new AdapterManager(array_keys($this->settings));
		$adapter = $adapterManager->resolve($adapterManager->getAdapter());
		$this->adapter = new $adapter($this->settings);
	}
	public function __destruct(){
		$this->adapter->__destruct();
	}
	public function get($key){
		return $this->adapter->get($key);
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