#Uniform Cache
##What is it?
This module provides a modular, extensible uniform caching interface to most caching platforms. This includes(in order of priorty):  

1. Dummy Cache   
1. File Cache  
1. MySQL (Coming soon)  
1. APC (Coming soon)
1. Shared Memory (Coming soon)  
1. Memcached (Coming soon)  
1. Redis (Coming soon)  

---
##For Users:
###Cache Method Summary:

- ``+ get(String $key, [function $generator])``  
	**Description:** *Returns the requested object from the cache, unless the item has expired and/or been removed in which case, providing the second parameter has not been provided, false is returned.*  
	**A node on read-through caching:**  
	The second (optional) paramter is a generator that can be passed in to supply the data being requested in the event that it is not present in the cache. 
	This should be done in the following way:*  
	<pre>
		$settings = array(
			'DiskAdapter' => array(
				'filename' => 'cache' //Creates a cache file called "cache.json";
			)
		);
		$myCache = new UniformCache\Cache($settings);
		$generator = function(){
			return array(
				'value'=>'TheCacheDoesntHaveMeSoThisFunctionProvidesMeInstead', //Some arbitary value, basically what you want to save to the cache.  
				'ttl' => 3600 //The ttl of your cached object. This is optional, however ommiting this will result in a cache object that never dies.
			);
		}
		echo $myCache->get('ImAskingForSomethingTheCacheDoesntHave', $generator); //Returns "TheCacheDoesntHaveMeSoThisFunctionProvidesMeInstead" as well as saving it to the cache.
	</pre>  
	**Returns:** ``Mixed``  

- ``+ set(String $key, Mixed $value, int $ttl)``  
	**Description:** *Stores an item (specified by ``$value``) in the cache under the key supplied by ``$key`` and (optionally) to expire after the number of seconds specified by ``$ttl``*  
	**Returns:** ``void``

- ``+ delete(String $key)``  
	**Description:** *Removes the item from the cache specified by ``$key``.*  
	**Returns:**  ``void``

- ``+ purge(void)``  
	**Description:** *Completely empties the cache.*  
	**Returns:** ``void``  

###How-To:  
1. Extract this library to your project folder.
1. Include Cache.php (e.g: ``include dirname(__FILE__) . './lib/Cache.php';``)
1. Create a new instance of Cache.php, passing in an array of adapter settings with the name of the adapter you wish to use as the key.  
An array of settings should be passed into the new instance of ``Cache()`` thus:  
	**NOTE: If the settings for a particular adapter are missing or the array key does not match that of the class and/or file, they will not be passed to the adapter**
	<pre>
	$settings = array(  
		'DiskAdapter' => array(  
			'filename' => 'cache'  //sets the filename of the cachefile.
		)  
	);
	$myCache = new UniformCache\Cache($settings);
	</pre>	
1. To set a cache item simply call ``$myCache->set($key, $value, $ttl)`` where ``$key`` is the key used to retrieve the data later, ``$value`` is the data you wish to store and the third paramter(optional) specifies the ttl (or time to live) of the item you wish to store. In other words - setting a ttl of 10 would keep the object for 10 seconds after which it would "expire" and be removed from the cache. To store a Date object for 10 seconds the following syntax would be used: ``$myCache->set('currentDateObject', new Date(), 10);``. After 10 seconds this object would expire and be removed from the cache.
1. To retrieve an item from the cache simply call ``$myCache->get('currentDateObject');``. In this case, this would return the date object we previously stored in the cache.

###Usage
<pre>
	//For file based caching
	$settings = array(
		'DiskAdapter'=>array(
			'filename' => 'foo' //creates a foo.json file in the cache folder
		)
	);
	$uc = new UniformCache\Cache($settings);
	$uc->set('foo', 'bar', 7200); //Will stay in the cache for 2 hours
	echo $uc->get('foo'); //Will return "bar" from the cache.
</pre>  

###Adapter Specific Settings And Format
<pre>
$settings = array(
	'DummyAdapter' => array(),   //Requires no parameters
	'DiskAdapter' => array(
		'filename' => 'foo'
	),
	'MySQLAdapter' => array(
		'host' => 'localhost',   //The hostname of your database server
		'port' => 3306 		     //The port of your database server, if you're not sure then 3306 is usually a good idea.
		'username' => 'root',    //Your database username. This is the username you use to connect to MySQL
		'password' => '', 	     //This is the password for your database user
		'database' => '',	     //This is the name of the database where your cache table is
		'table' => 'cache'	     //This is the name of your cache table.
	),
	'APCAdapter' => array(),     //Not yet implemented
	'SharedMemoryAdapter' => (), //Not yet implemented 
	'MemcachedAdapter' => (),	 //Not yet implemented
	'RedisAdapter' => ()		 //Not yet implemented
);
</pre>
---  

##For Developers:

###Writing Your Own Adapter
Uniform Cache is a modular and extensible caching framework and as such allows you to write your own adapters to support whatever caching methods you see fit.

1. Create a new PHP file in the "adapters" folder the name of the file(and the class) should be the title of the caching service you wish to support followed by the word "Adapter". This should be in title case. For example to support Foo you would name both the the class and the file "FooAdapter" and "FooAdapter.php" respectively.  
1. An interface is provided in the adapters folder named Adapter.interface.php. This should be implemented by your adapter along with all methods contained.  
	####Adapter Interface Method Summary:   
	- ``+ get(String $key);``  
		**Description:** *Returns the requested object from the cache, unless the item has expired and/or been removed in which case, false should be returned to indicate a cache miss.*  
		**Returns:** ``Mixed``

	- ``+ set(String $key, Mixed $value, int $ttl);``  
		**Description:** *Stores an item (specified by ``$value``) in the cache under the key supplied by ``$key`` and (optionally) to expire after the number of seconds specified by ``$ttl``*  
		**Returns:** ``void``

	- ``+ delete(String $key);``  
		**Description:** *Removes the item from the cache specified by ``$key``.*  
		**Returns:** ``void``  

	- ``+ purge();``  
		**Description:** *Completely empties the cache.*  
		**Returns:** ``void``

	- ``+`` <u>``getPriority``</u>``();``  
		**Description:** *Returns an integer denoting priorty of the adapter in question. This determines the order in which adapters are loaded and used. The first available adapter with the highest priorty is loaded by default. Therefore an adapter with a priority of 6 will be loaded in preference to an adapter with a priority of 5. This is to allow for fallback behaviour (e.g. When MySQL is unavailable fall back to file based caching)*  
		**Returns:** ``int``

	- ``+`` <u>``usable``</u>``();``  
		**Description:** *Returns a boolean indicating whether or not this adapter is currently usable. This behaves somewhat like an on/off switch. An adapter with this set to false will not be loaded. This is also useful to ensure that the MySQL connection is successful before loading the adapter(e.g: connect to MySQL, then have your adapter set this to ``true`` to allow the adapter to load).*  
		**Returns:** ``boolean``	
1. Finally, in order to be loaded correctly, your adapter must be in the ``UniformCache`` namespace.


#Licence
GPL. See [LICENCE](https://github.com/robertmain/UniversalCache/blob/master/LICENCE) for more info.