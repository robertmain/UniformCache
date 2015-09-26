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

namespace UniformCache\adapters;
require_once('Adapter.interface.php');

/**
 * MySQL
 *
 * MySQL Class - This class is used by the MySQL adapter to actually connect to and query MySQL.
 * 
 * @author Stewart Atkins
 * @package UniformCache
 * @copyright Stewart Atkins
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * 
 */
class MySQL_DB {
	
	/**
	 * The username to connect to MySQL with
	 * @var string
	 */
	private $user;

	/**
	 * The password to connect to MySQL with
	 * @var string
	 */
	private $pass;

	/**
	 * The hostname of the MySQL server
	 * @var string
	 */
	private $host;

	/**
	 * The port the MySQL server is running on (defaults to 3306)
	 * @var int
	 */
	private $port;

	/**
	 * The database to connect to
	 * @var string
	 */
	private $db;

	/**
	 * An optional name to give the connection to MySQL
	 * @var string
	 */
	private $friendlyname;

	/**
	 * An array of connection resource link identifiers for the connection
	 * @var array
	 */
	private static $links = Array();

	/**
	 * The last error to be raised by MySQL
	 * @var string
	 */
	private $lastError = "";

	/**
	 * The primary key ID of the last record inserted
	 * @var integer
	 */
	private $insertID = 0;

	/**
	 * Constructs the MySQL adapter
	 * @param string $hostname The hostname of the MySQL server
	 * @param int $port The port the MySQL server is running on (defaults to 3306)
	 * @param string $username The username to connect to MySQL with
	 * @param string $password The password to connect to MySQL with
	 * @param string $dbname The name of the database to connect to
	 * @param string $fname A friendly name to give the connection (if you wish)
	 */
	public function __construct($hostname,$port,$username,$password,$dbname,$fname = ""){
		$this->user = $username;
		$this->pass = $password;
		$this->host = $hostname;
		$this->port = $port;
		$this->db = $dbname;
		$this->friendlyname = $fname;
	}
	
	/**
	 * Constructs a key used to retrieve link resources
	 *
	 * The array (`self::$links`) is keyed using hostname, port, username with each element seperated by a douple pipe. This method generates a key that can be used to retrieve the current MySQL resource link
	 * 
	 * @return string A string used to key the `self::$links` array of MySQL resource links.
	 */
	private function getLinkKey(){
		return $this->host."||".$this->port."||".$this->user;
	}
	
	/**
	 * Returns a single link resource keyed from `self::$links`
	 *
	 * This method is used to facilitate connection pooling as it means that the class can support multiple connections to multiple servers simultaneously, but only ever opens one connection per server
	 * @return resource A link resource representing the MySQL connection
	 */
	private function getLink(){
		if(!isset(self::$links[$this->getLinkKey()])){	
			$this->createLink();
		}
		return self::$links[$this->getLinkKey()];
	}
	
	/**
	 * Escape Text
	 *
	 * Escapes any text passed in and enables it to be safely passed to MySQL without fear of SQL injection attacks
	 * 
	 * @param  string $text An untrusted string potentially containing malicious code
	 * @return string       A safe, escaped string, ready to be passed to MySQL for storage
	 */
	public function escape($text){
		return mysqli_real_escape_string($this->getLink(),$text);
	}
	
	/**
	 * Create Link
	 *
	 * Creates and stores a MySQL link resource in `self::$links` to facilitate connection pooling
	 */
	private function createLink(){
		$link = @mysqli_connect($this->host,$this->user,$this->pass,"",$this->port);
		if(mysqli_connect_error()){
			die("Error ".mysqli_connect_errno());
		}
		self::$links[$this->getLinkKey()] = $link;
		mysqli_set_charset($link,"utf8");
	}
	
	/**
	 * Execute a query
	 * @param  string $query An SQL query
	 * @return \UniformCache\adapters\MySQL_Result An object of type \UniformCache\adapters\MySQL_Result
	 */
	public function query($query){
		$constatus = mysqli_select_db($this->getLink(),$this->db);
		if(!$constatus){
			die("DB Connection '{$this->friendlyname}' - unable to attach to database - ".mysqli_error($this->getLink()));
		}
		
		$statement = @mysqli_prepare($this->getLink(),$query);
		if(!$statement){
			$this->lastError = mysqli_error($this->getLink());
			return false;
		}
			
		$formatstr = "";
		$params = Array();
		
		if(func_num_args() > 1){
			$args = Array();
			for($i=1;$i<func_num_args();$i++){
				$args[$i] = func_get_arg($i);
				$params[] = &$args[$i];
				if(is_double($args[$i]))
					$formatstr.="d";
				elseif(is_integer($args[$i]))
					$formatstr.="i";
				else
					$formatstr.="s";
			}
		}
		$real_params = array_merge(Array($statement,$formatstr),$params);
		@call_user_func_array("mysqli_stmt_bind_param",$real_params);
		$result = mysqli_stmt_execute($statement);
		
		$this->lastError = mysqli_error($this->getLink());
		$this->insertID = mysqli_insert_id($this->getLink());
		
		if($result){
			if(mysqli_stmt_result_metadata($statement)){
				return new MySQL_Result($statement);
			}else{
				return true;
			}
		}else{
			return false;
		}
	}
	
	/**
	 * Return last error
	 *
	 * Returns the last MySQL error to occur. Returns an empty string if no error ocurred.
	 * @return string The last MySQL error to occur. Empty string if none.
	 */
	public function getError(){
		return $this->lastError;
	}
	
	/**
	 * Return the ID of the last inserted record
	 * @return int The primary key value of the last inserted record
	 */
	public function insertID(){
		return $this->insertID;
	}
}

/**
 * MySQL Result
 *
 * MySQL result encapsulation class
 * 
 * @author Stewart Atkins
 * @package UniformCache
 * @copyright Stewart Atkins
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * 
 */	
class MySQL_Result{

	/**
	 * MySQLi prepared statement
	 * @var object A MySQLi statement object
	 */
	private $statement;

	/**
	 * Query results
	 * @var mixed The data returned by the query
	 */
	public $results;

	/**
	 * Turns query buffering on and off
	 * @var boolean
	 */
	private $buffered = false;
	
	/**
	 * Constructs the MySQL result class
	 * @param object $stmt A MySQLi statment
	 */
	public function __construct($stmt){
		$this->statement = $stmt;
		$fields = mysqli_fetch_fields(mysqli_stmt_result_metadata($this->statement));

		$this->buffer();//required?

		$this->results = Array();
		
		$params = Array($this->statement);
		foreach($fields as $field){
			$params[] = &$this->results[$field->name];
		}
		
		call_user_func_array("mysqli_stmt_bind_result",$params);
		
	}
	
	/**
	 * Get next result record as an associative array
	 * 
	 * @return array|boolean|null Returns false if no more results, or null if there was an error.
	 */
	public function fetch_assoc(){			
		$ret = @mysqli_stmt_fetch($this->statement);
		if($ret){
			$ret = Array();
			foreach($this->results as $k=>$v){
				$ret[$k] = $v;
			}
		}
		return $ret;
	}
	
	/**
	 * Returns the query results automatically in the most appropriate format
	 * @param  string $keycol 
	 * @param  string $valcol 
	 * @return mixed  The results of the query
	 */
	public function fetch_all($keycol = NULL, $valcol = NULL){
		$results = Array();
		
		while($r = $this->fetch_assoc()){
			if($keycol == NULL){
				$results[] = ($valcol == NULL ? $r : $r[$valcol]);
			}else{
				$results[$r[$keycol]] = ($valcol == NULL ? $r : $r[$valcol]);
			}
		}
		
		return $results;
	}
	
	/**
	 * Returns the number of rows in the result produced by execution of the query.
	 *
	 * **WARNING: ** This will cause the result set fo be buffered.
	 * @return int The number of rows of data returned by the query
	 */
	public function num_rows(){
		if(!$this->buffered)
			$this->buffer();
		return @mysqli_stmt_num_rows($this->statement);
	}
	
	//switch from unbuffered to a buffered query
	public function buffer(){
		if($this->buffered)
			return;
		@mysqli_stmt_store_result($this->statement);
		$this->buffered = true;
	}
}

/**
 * MySQL Adapter
 *
 * The MySQL adapter for Uniform Cache
 * 
 * @author Robert Main
 * @package UniformCache
 * @copyright Robert Main
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * 
 */
class MySQLAdapter implements Adapter{

	/**
	 * The settings array provided to the adapter by the adapter manager
	 * @var array
	 */
	private $config;
	
	/**
	 * An instance of the \UniformCache\adapters\MySQL_DB class
	 * @var \UniformCache\adapters\MySQL_DB
	 */
	private $db;
	
	/**
	 * Constructs the MySQL adapter
	 *
	 * Sets up the MySQL adapter by creating the nesecary table (if it doesn't already exist)
	 * @param array $settings An array of adapter settings in the following format:
	 * 
	 * | Key  | Description |
	 * | ------------- | ------------- |
	 * | `hostname` | The hostname of the MySQL server  |
	 * | `port` (optional) | The port the MySQL server is running on. This defaults to 3306  |
	 * | `username` | The username to connect to MySQL with  |
	 * | `password` | The password to connect to MySQL with  |
	 * | `database` | The database to connect to |
	 * | `table` | The database table to store cache entries in. If this does not exist, it will be created (provided the accoutn supplied has `CREATE` privaleges).   |
	 */
	public function __construct($settings){
		$this->config = $settings;
		$this->db = new MySQL_DB($this->config['hostname'], $this->config['port'], $this->config['username'], $this->config['password'], $this->config['database']);
		if(!$this->db->query("SELECT COUNT(*) as `0` FROM information_schema.TABLES WHERE `TABLE_NAME` = '" . $this->config['table'] . "' AND `TABLE_SCHEMA` = '" . $this->config['database'] . "' ")->fetch_assoc()[0]){
			$this->db->query("CREATE TABLE IF NOT EXISTS `" . $this->config['table'] . "` (`key` varchar(255) NOT NULL,`value` longtext,`expiresAt` varchar(255) NOT NULL, PRIMARY KEY (`key`));");
		}
	}

	/**
	 *
	 * Method stub for `get` method in other adapters
	 * 
	 * The third argument to this method provides the required data, in the event that this is not available in the cache already(basically, a read through cache).
	 * 
	 * @param  mixed $key The key to be used to retrieve the requested data from the cache
	 * @return boolean Boolean false is always returned since we never have any data stored to return
	 */
	public function get($key){
		$result = $this->db->query("SELECT * FROM " . $this->config['database'] . "." . $this->config['table'] . " WHERE `key` = ? ", $key)->fetch_assoc();
		if(($result['expiresAt'] <= time()) && ($result['expiresAt'] != 0)){
			$this->delete($key);
			return false;
		}
		else{
			return json_decode($result['value'], true);
		}
	}
	
	/**
	 *
	 * Save an item to the cache.
	 * 
	 * Save an item to the cache for the time specificed by the `$ttl` parameter
	 * 
	 * @param mixed $key The key used to store the cache data
	 * @param mixed $value The cache data
	 * @param int $ttl The time `$value` should be persisted in the cache for (in seconds).
	 */
	public function set($key, $value, $ttl=0){
		$value = json_encode($value);
		if($ttl != 0){
			$ttl = time() + $ttl;
		}
		$this->db->query("INSERT INTO " . $this->config['database'] . '.' . $this->config['table'] . " (`key`, `value`, `expiresAt`) VALUES(?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `expiresAt` = VALUES(`expiresAt`)", $key, $value, $ttl);
	}

	/**
	 *
	 * Remove one item
	 *
	 * Removes an item (specified by `$key`) from the cache
	 * 
	 * @param mixed $key The key of the data you wish to remove from the cache
	 */
	public function delete($key){
		$this->db->query("DELETE FROM " . $this->config['database'] . '.' . $this->config['table'] . " WHERE `key` = ?", $key);
	}

	/**
	 * Purge the cache
	 *
	 * Purges the cache completely. On shared resources such as APC or shared memory - a prefix is used when adding items to the cache. This same prefix is then used to avoid collisions when purging(for some reason other users of shared servers tend not to like having their cache forcibly purged by another user...)
	 */
	public function purge(){
		$this->db->query("TRUNCATE " . $this->config['table']);
	}

	/**
	 * Get Adaper Priorty
	 *
	 * Returns the adapter priorty. Certain adapters take priority over others. For example, the DummyAdapter has the highest priorty(1) because it is always available. Higher priority adapters are examined first to determine if they are instanciable.
	 * @return int The priroty of the current adapter. A lower number has a higher priorty.
	 */
	public static function getPriority(){
		return 3;
	}

	/**
	 * Check If This Adapter Is Usable
	 *
	 * Check to see if the adapter is able to be instanciated. Some adapters turn this on and off based on certain conditions. For example, the MySQL adapter might check to see if MySQL is installed and/or enabled and set this to `false` if not. This would prevent UniformCache from trying to instanciate the MySQL adapter.
	 * @return  boolean Value representing whether or not the adapter can be instanciated
	 */
	public static function usable(){
		return true;
	}
}
