<?php
namespace UniformCache;
require('Adapter.interface.php');
class APCAdapter implements Adapter{
	private $settings;
	public function __construct($settings){
		$this->settings = $settings;
		if(!array_key_exists('prefix', $this->settings)){
			$this->settings['prefix'] = __NAMESPACE__;
		}
		$this->settings['prefix'] = $this->settings['prefix'] . '_';
	}
	public function get($key){
		error_reporting(E_ALL ^ E_NOTICE);
		$result = \apc_fetch($this->settings['prefix'] . $key);
		var_dump(error_get_last());
		return $result;
	}
	public function set($key, $value, $ttl){
		apc_store($this->settings['prefix'] . $key, $value, $ttl);
	}
	public function delete($key){
		apc_delete($this->settings['prefix'] . $key);
	}
	public function purge(){

	}
	public static function getPriority(){
		return 4;
	}
	public static function usable(){
		if(extension_loaded('apc') && ini_get('apc.enabled')){
			return true;
		}
		else{
			return false;
		}
	}
}