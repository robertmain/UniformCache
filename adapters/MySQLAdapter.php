<?php
namespace UniformCache;
use Exception;
class MySQL_DB {
	private $user;
	private $pass;
	private $host;
	private $port;
	private $db;
	private $friendlyname;
	private static $links = Array();
	private $lastError = "";
	private $insertID = 0;
	
	public function __construct($hostname,$port,$username,$password,$dbname,$fname = ""){
		$this->user = $username;
		$this->pass = $password;
		$this->host = $hostname;
		$this->port = $port;
		$this->db = $dbname;
		$this->friendlyname = $fname;
	}
	
	private function getLinkKey(){
		return $this->host."||".$this->port."||".$this->user;
	}
	
	private function getLink(){
		if(!isset($this->links[$this->getLinkKey()]))
			$this->createLink();
		return $this->links[$this->getLinkKey()];
	}
	
	public function escape($text){
		return mysqli_real_escape_string($this->getLink(),$text);
	}
	
	private function createLink(){
		$link = @mysqli_connect($this->host,$this->user,$this->pass,"",$this->port);
		if(mysqli_connect_error()){
			die("Error ".mysqli_connect_errno());
		}
		$this->links[$this->getLinkKey()] = $link;
		mysqli_set_charset($link,"utf8");
	}
	
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
	
	public function getError(){
		return $this->lastError;
	}
	
	public function insertID(){
		return $this->insertID;
	}

	public function tableExists($table){
		$query = "SELECT COUNT(*) FROM information_schema.tables WHERE TABLE_NAME = 'test." . $table ."'";
		$statement = @mysqli_prepare($this->getLink(),$query);
		var_dump(mysqli_stmt_execute($statement));
		if(mysqli_stmt_execute($statement)){
			return new MySQL_Result($statement);
		}
	}
}
	
class MySQL_Result{
	private $statement;
	public $results;
	private $buffered = false;
	
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
	
	//get next result record as an associative array
	//returns false if no more results, or null on error
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
	
	//returns number of rows in result set
	//WARNING: will cause result set to be buffered
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

class MySQLAdapter implements Adapter{
	private $db;
	private $table;
	public function __construct($settings){
		$this->table = $settings['table'];
		if($this->db = new MySQL_DB($settings['hostname'], $settings['port'], $settings['username'], $settings['password'], $settings['database'])){
			if(!$res = $this->db->tableExists($this->table)){
				echo "TABLE DOES NOT EXIST!";
			}
			else{
				var_dump($this->db->tableExists($this->table));
			}
		}
	}
	public function get($key){
		$query = $this->db->query("SELECT * FROM " . $this->table . " WHERE `key` = ?", $key);
		$results = array();
		while($result = $query->fetch_assoc()){
			$results[] =$result;
		}
		return json_decode($result[0]);
	}
	public function set($key, $value, $ttl){

	}
	public function delete($key){

	}
	public function purge(){

	}
	public static function getPriority(){
		return 1;
	}
	public static function usable(){
		return true;
	}
}