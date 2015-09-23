<?php
namespace UniformCache;
use Exception;
class AdapterManager{
	private $adapters = array();
	public function __construct($adapters){
		if(!$adapters){
			throw new Exception("Settings required, none given in constructor of " . basename(__FILE__));
		}
		$this->adapters = $adapters;
	}
	public function getAdapter(){
		foreach($this->adapters as $adapter => $settings){
			if($this->isValid($adapter)){
				return new $this->resolve($adapter($settings));
			}
		}
		throw new Exception("No Suitable Adapter Found");
	}
	public function resolve($class){
		return __NAMESPACE__ . "\\adapters\\" . $class;
	}
	public function isValid($adapter){
		if(array_key_exists($adapter, $this->adapters)){
			$fullyQualifiedAdapter = $this->resolve($adapter);
			return $fullyQualifiedAdapter::usable();
		}
		else{
			return false;
		}
	}
}