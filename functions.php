<?php
require_once ("config.php");

class MyQueryBuilder{
	const CONNNECT_EXCEPTION_FILE = 'exception_connect.txt';
	const QUERY_EXCEPTION_FILE = 'exception_query.txt';
	const METHOD_EXCEPTION_FILE = 'exception_method.txt';
	private $record = '';
	private $val_array = NULL;
	public $db;
	public $STH;
	
	public function __construct(){
		$dsn = 'mysql:host='.HOST.';dbname='.DB_NAME;
		$file = __DIR__.CONNNECT_EXCEPTION_FILE;
		try{
			$this->db = new PDO($dsn,USER,PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch(PDOException $e) {  
			echo "Ошибка подключения";
			file_put_contents($file,$e->getMessage()."\r\n",FILE_APPEND);
		}
	}
		
	public function delete (){
		$this->record = 'DELETE ';
		return $this;
	}
	public function insert ($table,$what){
		if (is_array($what)){
			if (count ($what)>1){
				$this->record = 'INSERT INTO ' .$table .' ' .'(' .implode (', ',$what) .')';
			}
		}
		else{
			$this->record = 'INSERT INTO ' .$table .' ' .'(' .$what .')';
		}
		return $this;
	}
	public function values ($val){
		if (is_array($val)){
			$this->val_array=$val;
			$count = count ($val);
			if ($count>1){
				$str = '';
				for ($i=0; $i<$count; $i++){
					$str = $str .'?, ';
				}
				$str = substr($str, 0, -2);
				$this->record = $this->record .' VALUES ' .'(' .$str .')';
			}
		}
		else{
			$this->val_array[]=$val;
			$this->record = $this->record .' VALUES ' .'(' .'?' .')';
		}
		return $this;
		
	}
	public function select($columns){
		if (is_array($columns)){
			if (count ($columns)>1){
				$this->record = 'SELECT ' . implode (', ',$columns);
			}
		}
		else{
			$this->record = 'SELECT ' .$columns;
		}
		return $this;
	}
	public function from($table){
		if ((!strstr($this->record,'DELETE'))&&(!strstr($this->record,'SELECT'))){
			echo 'Ошибка метода from';
		}
		else{
		$this->record = $this->record .' FROM ' .$table;
		return $this;
		}
	}
	public function update($table){
		$this->record = 'UPDATE ' .$table;
		return $this;
	}
	public function set($what){
		$this->record = $this->record . ' SET ';
		$this->val_array=array_values($what);
		if (count($what)>1){
			foreach ($what as $key=>$value){
				$this->record = $this->record .$key .' = ' .'?'.', ';
			}
			$this->record = substr($this->record, 0, -2);
		}
		else{
			$this->record = $this->record . ' SET ' .$what .' = ' .'?' ;
		}
		return $this;
	}
	public function where($what,$oper,$val){
		$b = array ("=", ">", "<", ">=", "<=", "<>", "LIKE");
		$key = array_search ($oper, $b);
		$oper = $b[$key];
		$this->val_array[]= $val;
		$this->record = $this->record .' WHERE ' .$what .' '.$oper.' '.'?';
		return $this;		
	}
	public function and_where($what,$oper,$val){
		$b = array ("=", ">", "<", ">=", "<=", "<>", "LIKE");
		$key = array_search ($oper, $b);
		$oper = $b[$key];
		$this->val_array[]= $val;
		$this->record = $this->record .' AND ' .$what .' '.$oper.' '.'?';
		return $this;		
	}
	public function or_where($what,$oper,$val){
		$b = array ("=", ">", "<", ">=", "<=", "<>", "LIKE");
		$key = array_search ($oper, $b);
		$oper = $b[$key];
		$this->val_array[]= $val;
		$this->record = $this->record .' OR ' .$what .' '.$oper.' '.'?';
		return $this;	
	}
	public function limit($limit){
		$limit = intval($limit);
		$this->record = $this->record .' LIMIT ' .$limit;
		return $this;
	}
	public function offset ($offset){
		$offset = intval ($offset);
		if (!strstr($this->record,'LIMIT')){
			echo 'Ошибка метода offset';
		}
		else{
			$this->record = $this->record .' OFFSET ' .$offset;
		}
		return $this;
	}
	public function order ($columns, $por){
		$orders = array("ASC","DESC");
		$key = array_search ($por, $orders);
		$orderby = $orders[$key];
		if (is_array($columns)){
			if (count ($columns)>1){
				$this->record = $this->record .' ORDER BY ' . implode (', ',$columns) .' ' .$orderby;
			}
		}
		else{
			$this->record = $this->record .' ORDER BY ' .$columns .' ' .$orderby;
		}
		return $this;
	}
	
	public function query(){
		$this->STH = $this->db->prepare($this->record);
		$this->STH->execute($this->val_array);
		return $this;
	}	
	public function getResult(){
		$result = $this->STH->fetchAll();
		print_r($result);
	}
		
}
?>