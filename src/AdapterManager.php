<?php

/**
 * This file is part of Uniform Cache.
 * 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * 
 */

namespace UniformCache;
use Exception;

/**
 * Adapter Manager
 *
 * Manages the instanciation of adapters as well as checking to ensure that adapters are available.
 * 
 * @author Robert Main
 * @package UniformCache
 * @copyright  Robert Main
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * 
 */
class AdapterManager{

	/**
	 * Adapter settings
	 *
	 * 
	 * An array of adapter settings keyed by the name of the adapter they belong to. For example the APCAdapter will be passed any settings under the key of `APC`
	 * 
	 * @var array
	 */
	private $adapters = array();

	/**
	 * Constructs the adapter manager
	 *
	 * The adapter manager constructor is responsible for checking that a valid array of settings have been provided.
	 * 
	 * @param array $adapters An array of adapters of type \UniformCache\Adapter
	 * @throws \Exception An exception is thrown if no adapters are specified
	 */
	public function __construct($adapters){
		if(!$adapters){
			throw new Exception("Settings required, none given in constructor of " . basename(__FILE__));
		}
		$this->adapters = $adapters;
	}

	/**
	 * Instanciates an adapter
	 *
	 * Instanciates an adapter and passes it's constructor the settings provided by the user of the library.
	 * 
	 * @return \UniformCache\Adapter A cache adapter
	 */
	public function getAdapter(){
		foreach($this->adapters as $adapter => $settings){
			if($this->isValid($adapter)){
				return new $this->resolve($adapter($settings));
			}
		}
		throw new Exception("No Suitable Adapter Found");
	}

	/**
	 * Resolve class to FQN
	 *
	 * Resolves a classname(e.g APCAdapter) to a fully qualified class name (e.g \UniformCache\APCAdapter).
	 * 
	 * @param  string $class The name of the class to resolve to a fully qualified class name
	 * @return string A fully qualified class name
	 */
	public function resolve($class){
		return __NAMESPACE__ . "\\adapters\\" . $class;
	}

	/**
	 * Check the usability of an adapter prior to instanciation
	 *
	 * This is used to check that an adapter can be instanciated before it actually is instanciated. This is useful for example to check that MySQL installed and we can connect before we instanciate a MySQL adapter.
	 * 
	 * @param  \UniformCache\Adapter $adapter A cache adapter
	 * @return boolean
	 */
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