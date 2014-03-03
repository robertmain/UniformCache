<?php
namespace UniformCache;
use Exception;
class AdapterManager{
	private $adapters = array();
	private $settingsKeys = array();
	public function __construct($settingsKeys){
		if(!$settingsKeys){
			throw new Exception("Settings required, none given in constructor of " . basename(__FILE__));
		}
		$this->settingsKeys = $settingsKeys;
	}
	public function getAdapter(){
		$this->adapters = glob(dirname(__FILE__) . '/adapters/*Adapter.php');
		$adapters = array();
		foreach($this->adapters as $adapter){
			include_once $adapter;
			$class = substr(basename($adapter), 0, -4);
			$fqcn = $this->resolve($class);
			$classPriority = $fqcn::getPriority();
			if(array_key_exists($classPriority, $adapters)){
				if(!is_array($adapters[$classPriority])){
					$duplicateKey = $adapters[$classPriority];
					$adapters[$classPriority] = array($duplicateKey);
				}
				$adapters[$classPriority][] = $class;
			}
			else{
				$adapters[$classPriority] = $class;
			}
		}
		krsort($adapters);
		foreach($adapters as $key => $adapter){

			if(is_array($adapter)){
				foreach($adapter as $subAdapter){
					if($this->isValid($subAdapter)){
						return $subAdapter;
					}
				}
			}
			else{
				if($this->isValid($adapter)){
					return $adapter;
				}
			}
		}
		throw new Exception("No valid adapter available.");
	}
	public function resolve($class){
		return __NAMESPACE__ . "\\" . $class;
	}
	public function isValid($adapter){
		if(in_array($adapter, $this->settingsKeys)){
			$fullyQualifiedAdapter = $this->resolve($adapter);
			return $fullyQualifiedAdapter::usable();
		}
		else{
			return false;
		}
	}
}