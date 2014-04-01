<?php
namespace UniformCache;
require_once('Adapter.interface.php');
class RedisAdapter implements Adapter {
	private $connection;
	private $settings;
	public function __construct($settings) {
		$this->settings = $settings;
		$this->connection = $this->connect();
	}

	private function connect() {
		$connection = new \Redis();	
		if(isset($this->settings['server']) && isset($this->settings['port'])) {
			$connection->connect($this->settings['server'], $this->settings['port']);
		}else{
			$connection->connect($this->settings['server']);
		}

		if(isset($this->settings['password'])) {
			$connection->auth($this->settings['password']);
		}

		if(isset($this->settings['database'])) {
			$connection->select($this->settings['database']);
		}
		$this->connection = $connection;
	}

	public function get($key) {
		return $this->connection->get($key);
	}

	public function set($key, $value, $ttl) { 
		$this->connection->set($key, $value, $ttl);
	}

	public function delete($key) {
		$this->connection->delete($key);
	}

	public function purge() {
		$this->connection->flushDB();
	}

	public static function getPriority(){
		return 7;
	}

	public static function usable(){
		return class_exists('Redis');
	}
}
