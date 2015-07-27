<?php
require_once ("config.php");
const QUERY_EXCEPTION_FILE = '\exception_query.txt';
const METHOD_EXCEPTION_FILE = '\exception_method.txt';

class MyQueryBuilderException extends Exception{
	public function __construct($message, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
    }
	public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
/**
* file_exception_method запись исключений методов в файл
*/
	public function file_exception_method(){
		$file = __DIR__.METHOD_EXCEPTION_FILE;
		file_put_contents($file,$this->getMessage()."\r\n" .'Code: '.$this->getCode()."\r\n"
								."File: ".$this->getFile()."\r\n" ."Line: ".$this->getLine()."\r\n"."\r\n",FILE_APPEND);
	}
/**
* file_exception_method запись исключений запросов в файл
*/
	public function file_exception_query(){
		$file = __DIR__.QUERY_EXCEPTION_FILE;
		file_put_contents($file,$this->getMessage()."\r\n" .'Code: '.$this->getCode()."\r\n"
								."File: ".$this->getFile()."\r\n" ."Line: ".$this->getLine()."\r\n"."\r\n",FILE_APPEND);
	}
	
}
class MyQueryBuilder{
/**
* $record строка формирования запроса
* $val_array массив prepared statement 
* $db дескриптор базы данных
* $STH дескриптор состояния
*/
	private $record = '';
	private $val_array = NULL;
	public $db;
	public $STH;
/**
*__construct cоздание экземпляра класса PDO и подключение к базе данных
*/	
	public function __construct(){
		$dsn = 'mysql:host='.HOST.';dbname='.DB_NAME;
		$this->db = new PDO($dsn,USER,PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
/**
*delete формирование строки запроса с ключевым словом DELETE 
*/		
	public function delete (){
		$this->record = 'DELETE ';
		return $this;
	}
/**
*insert формирование строки запроса с ключевым словом INSERT
*@param $table string таблица для записи
*@param $what поле(я) для записи 
*/
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
/**
*values формирование строки запроса с ключевым словом VALUES
*@param $val значение(я) для записи
*выбросит исключение без использования метода insert
*/
	public function values ($val){
		if (!strstr($this->record, 'INSERT')){
			throw new MyQueryBuilderException ('Отсутствует INSERT');
		}
		else{
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
	}
/**
*select формирование строки запроса с ключевым словом SELECT
*@param $columns поля для выборки
*/
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
/**
*from формирование строки запроса с ключевым словом FROM
*@param $table string таблица для выборки
*выбросит исключение без использования метода select или delete 
*/
	public function from($table){
		if ((!strstr($this->record,'DELETE'))&&(!strstr($this->record,'SELECT'))){
			throw new MyQueryBuilderException ('Отсутствует DELETE или SELECT');
		}
		
		$this->record = $this->record .' FROM ' .$table;
		return $this;
		
	}
/**
*update формирование строки запроса с ключевым словом update
*@param $table таблица для записи
*/
	public function update($table){
		$this->record = 'UPDATE ' .$table;
		return $this;
	}
/**
*set формирование строки запроса с ключевым словом SET
*@param $what array: $key имя поля, $value значение для записи
*выбросит исключение без использования метода update
*/	
	public function set($what){
		if (!strstr($this->record, 'UPDATE')){
			throw new MyQueryBuilderException ('Отсутствует UPDATE');
		}
		else{
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
	}
/**
*what формирование строки запроса с ключевым словом WHAT
*@param $what имя поля
*@param $oper оператор сравнения
*@param $val знаение для сравнения
*$b array "белый список" операторов сравнения
*выбросит исключение если при записи строка запроса будет пустой
*/	
	public function where($what,$oper,$val){
		if (!$this->record){
			throw new MyQueryBuilderException ('Невозможно записать WHERE');
		}
		else{
			$b = array ("=", ">", "<", ">=", "<=", "<>", "LIKE");
			$key = array_search ($oper, $b);
			$oper = $b[$key];
			$this->val_array[]= $val;
			$this->record = $this->record .' WHERE ' .$what .' '.$oper.' '.'?';
			return $this;	
		}
	}
/**
*and_where формирование строки запроса с использованием ключевого слова WHERE и оператора AND
*@param $what имя поля
*@param $oper оператор сравнения
*@param $val знаение для сравнения
*$b array "белый список" операторов сравнения
*выбросит исключение без использования метода where
*/
	public function and_where($what,$oper,$val){
		if (!strstr ($this->record, 'WHERE')){
			throw new MyQueryBuilderException ('Отсутствует WHERE');
		}
		else{
			$b = array ("=", ">", "<", ">=", "<=", "<>", "LIKE");
			$key = array_search ($oper, $b);
			$oper = $b[$key];
			$this->val_array[]= $val;
			$this->record = $this->record .' AND ' .$what .' '.$oper.' '.'?';
			return $this;
		}
	}
/**
*or_where формирование строки запроса с использованием ключевого слова WHERE и оператора OR
*@param $what имя поля
*@param $oper оператор сравнения
*@param $val знаение для сравнения
*$b array "белый список" операторов сравнения
*выбросит исключение без использования метода where
*/
	public function or_where($what,$oper,$val){
		if (!strstr ($this->record, 'WHERE')){
			throw new MyQueryBuilderException ('Отсутствует WHERE');
		}
		else{
			$b = array ("=", ">", "<", ">=", "<=", "<>", "LIKE");
			$key = array_search ($oper, $b);
			$oper = $b[$key];
			$this->val_array[]= $val;
			$this->record = $this->record .' OR ' .$what .' '.$oper.' '.'?';
			return $this;
		}
	}
/**
*limit формирование строки запроса с ключевым словом LIMIT
*@param limit int лимит запроса
*выбросит исключение если при записи строка запроса будет пустой 
*/
	public function limit($limit){
		if (!$this->record){
			throw new MyQueryBuilderException ('Невозможно записать LIMIT');
		}
		else{
			$limit = intval($limit);
			$this->record = $this->record .' LIMIT ' .$limit;
			return $this;
		}
	}
/**
*offset формирование строки запроса с ключевым словом OFFSET
*@param offset int значение смещения
*выбросит исключение без использования метода limit
*/
	public function offset ($offset){
		if (!strstr($this->record, 'LIMIT')){
			throw new MyQueryBuilderException ('Отсутствует LIMIT');
		}
		else{
			$offset = intval ($offset);
			$this->record = $this->record .' OFFSET ' .$offset;
			return $this;
		}
	}
/**
*order формирование строки запроса с ключевым словом ORDER
*@param $columns поля для сортировки
*@param $por порядок сортировки, если не указан то по умолчанию ASC 
*$orders array "белый список" значений сортировки
*/
	public function order ($columns, $por){
		if (!$this->record){
			throw new MyQueryBuilderException ('Невозможно записать ORDER');
		}
		else{
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
	}
/**
*query выполнение запроса 
*/	
	public function query(){
		
		$this->STH = $this->db->prepare($this->record);
		$this->STH->execute($this->val_array);
		
		return $this;
	}
/**
*getResult получение результатов запроса 
*/	
	public function getResult(){
		$result = $this->STH->fetchAll();
		print_r($result);
	}
		
}
?>