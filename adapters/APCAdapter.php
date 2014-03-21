<?php
namespace UniformCache;
use APCIterator;
use Exception;
require_once('Adapter.interface.php');
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
		return json_decode(\apc_fetch($this->settings['prefix'] . $key), true);
	}
	public function set($key, $value, $ttl){
		apc_store($this->settings['prefix'] . $key, json_encode($value), $ttl);
	}
	public function delete($key){
		apc_delete($this->settings['prefix'] . $key);
	}
	public function purge(){
		foreach(new \APCIterator('user', '#^' . $this->settings['prefix'] . '#', APC_ITER_KEY) as $entry) {
		    apc_delete($entry); //We can't use $this->delete() because the prefix is appended to the key in the method.
		}
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