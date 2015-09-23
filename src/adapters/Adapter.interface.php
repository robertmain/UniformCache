<?php
namespace UniformCache\adapters;
interface Adapter{
	public function get($key);
	public function set($key, $value, $ttl);
	public function delete($key);
	public function purge();
	public static function getPriority();
	public static function usable();
}
