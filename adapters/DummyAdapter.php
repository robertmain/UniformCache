<?php
namespace UniformCache;
require_once('Adapter.interface.php');
class DummyAdapter implements Adapter{
	public function get($key){
		return false;
	}
	public function set($key, $value, $ttl){}
	public function delete($key){}
	public function purge(){}
	public static function getPriority(){
		return 1;
	}
	public static function usable(){
		return true;
	}
}