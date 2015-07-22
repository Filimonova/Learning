<?php
require_once ("config.php");
class MyQueryBuilder{
	private $record = '';
	public $db;
	public function __construct(){
		$dsn = 'mysql:host='.HOST.';dbname='.DB_NAME;
		$file = 'Z:\home\localhost\www\example\exceptions_connect.txt';
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
		if (strstr($this->record,'INSERT')==false){
			echo 'Ошибка метода values';
		}
		else{
		if (is_array($val)){
			if (count ($val)>1){
				$this->record = $this->record .' VALUES ' .'("' .implode ('", "',$val) .'")';
			}
		}
		else{
			$this->record = $this->record .' VALUES ' .'("' .$val .'")';
		}
		return $this;
		}
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
		if ((strstr($this->record,'DELETE')==false)&&(strstr($this->record,'SELECT')==false)){
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
	public function set($what, $val){
		if (strstr($this->record,'UPDATE')==false){
			echo 'Ошибка метода offset';
		}
		else{
		if (is_array($what)){
			$this->record = $this->record . ' SET ';
			foreach ($what as $key=>$value){
				$this->record = $this->record .$key .' = ' ."'".$value."', ";
			}
			$this->record = substr($this->record, 0, -2);
		}
		else{
			$this->record = $this->record . ' SET ' .$what .' = ' ."'".$val ."'";
		}
		return $this;
		}
	}
	public function where($what,$oper,$val){
		$b = array ("=", ">", "<", ">=", "<=", "<>", "LIKE");
		$key = array_search ($oper, $b);
		$oper = $b[$key];
		if (is_int ($val)){
				$this->record = $this->record . ' WHERE ' .$what .$oper .$val;	
			}
		else {
			$this->record = $this->record .' WHERE ' .$what .' '.$oper.' '."'".$val."'";
		}
		return $this;		
	}
	public function and_where($what,$oper,$val){
		$b = array ("=", ">", "<", ">=", "<=", "<>", "LIKE");
		$key = array_search ($oper, $b);
		$oper = $b[$key];
		if (is_int ($val)){
				$this->record = $this->record . ' AND ' .$what .$oper .$val;	
			}
		else {
			$this->record = $this->record .' AND ' .$what .' '.$oper.' '."'".$val."'";
		}
		return $this;		
	}
	public function or_where($what,$oper,$val){
		$b = array ("=", ">", "<", ">=", "<=", "<>", "LIKE");
		$key = array_search ($oper, $b);
		$oper = $b[$key];
		if (is_int ($val)){
				$this->record = $this->record . ' OR ' .$what .$oper .$val;	
			}
		else {
			$this->record = $this->record .' OR ' .$what .' '.$oper.' '."'".$val."'";
		}
		return $this;	
	}
	public function limit($limit){
		$limit = intval($limit);
		$this->record = $this->record .' LIMIT ' .$limit;
		return $this;
	}
	public function offset ($offset){
		$offset = intval ($offset);
		if (strstr($this->record,'LIMIT')==false){
			echo 'Ошибка метода offset';
		}
		else{
			$this->record = $this->record ." OFFSET " .$offset;
		}
		return $this;
	}
	//TODO: порядок по умолч
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
	$file = '__DIR__.''Z:\home\localhost\www\example\exceptions_query.txt';
		//$this->record = $this->db->quote($this->record);
		try{
			if (strstr($this->record,'SELECT')==false){
				$result = $this->db->exec($this->record);
				echo "Количество затронутых строк = " .$result;
			}
			else{
				$result = $this->db->query ($this->record);
				while ($row = $result->fetch(PDO::FETCH_ASSOC)){
					print_r($row);
				}	
			}
			$this->record = '';
		}
		catch (PDOException $e){
			echo "Ошибка выполнения запроса";
			file_put_contents($file,$e->getMessage()."\r\n",FILE_APPEND);
		}	
	}	
		/* 
		$error_array = $this->db->errorInfo();
		if($this->db->errorCode() != 0000){
			echo "SQL ошибка: " . $error_array[2] . '<br />';
			} */

}
?>