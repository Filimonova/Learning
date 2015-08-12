<?php
require_once ("config.php");

class MyExceptionMethod extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null) 
	{
		parent::__construct($message, $code, $previous);
		if (WRITE_METHOD_EXCEPTION_FILE == 1){
			$this->writeExceptionFile();
		}
		else {
			echo 'невозможно записать файл';
		}
    }
    public function __toString() 
	{
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
	private function writeExceptionFile()
	{
		$file = __DIR__.METHOD_EXCEPTION_FILE;
	        file_put_contents($file,$this->getMessage()."\r\n" .'Code: '.$this->getCode()."\r\n"
								."File: ".$this->getFile()."\r\n" ."Line: ".$this->getLine()
								."\r\n"."\r\n",FILE_APPEND);
	}
}

class MyExceptionQuery extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null) 
	{
	    parent::__construct($message, $code, $previous);
		if (WRITE_QUERY_EXCEPTION_FILE == 1){
			$this->writeExceptionFile();
		}
    }
    public function __toString() 
	{
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
	private function writeExceptionFile()
	{
		$file = __DIR__.QUERY_EXCEPTION_FILE;
	        file_put_contents($file,$this->getMessage()."\r\n" .'Code: '.$this->getCode()."\r\n"
								."File: ".$this->getFile()."\r\n" ."Line: ".$this->getLine()
								."\r\n"."\r\n",FILE_APPEND);
	}
}

class MyExceptionConnect extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null) 
	{
	    parent::__construct($message, $code, $previous);
		if (WRITE_CONNECT_EXCEPTION_FILE == 1){
			$this->writeExceptionFile();
		}
    }
    public function __toString() 
	{
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
	private function writeExceptionFile()
	{
		$file = __DIR__.CONNECT_EXCEPTION_FILE;
	        file_put_contents($file,$this->getMessage()."\r\n" .'Code: '.$this->getCode()."\r\n"
								."File: ".$this->getFile()."\r\n" ."Line: ".$this->getLine()
								."\r\n"."\r\n",FILE_APPEND);
	}
}

class MyQueryBuilder
{
/**
* $record строка формирования запроса
* $val_array массив prepared statement 
* $count_row количество строк затронутых в запросе
*/
    private $record = '';
    private $val_array = NULL;
	private $count_row = NULL;
    public $db;
    public $sth;
/**
*__construct cоздание экземпляра класса PDO и подключение к базе данных
*/	
    public function __construct()
	{ 
	    $dsn = 'mysql:host='.HOST.';dbname='.DB_NAME;
		$this->db = new PDO($dsn,USER,PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		if (!isset($this->db)){
			throw new MyExceptionConnect ('Не удалось подключиться к базе данных');
		}
	    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}
/**
*delete формирование строки запроса с ключевым словом DELETE 
*/		
    public function delete ()
	{
	    $this->record = 'DELETE ';
	    return $this;
	}
/**
*insert формирование строки запроса с ключевым словом INSERT
*@param $table string таблица для записи
*@param $what поле(я) для записи 
*/
    public function insert ($table,$what)
	{
	    if (is_array($what)){
		    if (count ($what)>1){
			    $this->record = 'INSERT INTO ' .$table .' ' .'(' .implode (', ',$what) .')';
			}
		} else{
		    $this->record = 'INSERT INTO ' .$table .' ' .'(' .$what .')';
		}
	    return $this;
	}
/**
*values формирование строки запроса с ключевым словом VALUES
*@param $val значение(я) для записи
*выбросит исключение без использования метода insert
*/
    public function values ($val)
	{
	    if (!strstr($this->record, 'INSERT')){
		    throw new MyExceptionMethod ('Отсутствует INSERT');
		} else{
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
			} else{
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
    public function select($columns)
	{
	    if (is_array($columns)){
		    if (count ($columns)>1){
				$this->record = 'SELECT ' . implode (', ',$columns);
			}
		} else{
		    $this->record = 'SELECT ' .$columns;
		}
	    return $this;
	}
/**
*from формирование строки запроса с ключевым словом FROM
*@param $table string таблица для выборки
*выбросит исключение без использования метода select или delete 
*/
    public function from($table)
	{
	    if ((!strstr($this->record,'DELETE'))&&(!strstr($this->record,'SELECT'))){
		    throw new MyExceptionMethod ('Отсутствует DELETE или SELECT');
		}
	    $this->record = $this->record .' FROM ' .$table;
	    return $this;	
	}
/**
*update формирование строки запроса с ключевым словом update
*@param $table таблица для записи
*/
    public function update($table)
	{
	    $this->record = 'UPDATE ' .$table;
	    return $this;
	}
/**
*set формирование строки запроса с ключевым словом SET
*@param $what array: $key имя поля, $value значение для записи
*выбросит исключение без использования метода update
*/	
    public function set($what)
	{
	    if (!strstr($this->record, 'UPDATE')){
		    throw new MyExceptionMethod ('Отсутствует UPDATE');
		} else{
		    $this->record = $this->record . ' SET ';
		    $this->val_array=array_values($what);
			    if (count($what)>1){
				    foreach ($what as $key=>$value){
					    $this->record = $this->record .$key .' = ' .'?'.', ';
					}
				    $this->record = substr($this->record, 0, -2);
				} else{
				    $this->record = $this->record . ' SET ' .$what .' = ' .'?' ;
				}
		}
        return $this;		
	}
  /**
*where формирование строки запроса с ключевым словом WHERE
*@param $what имя поля
*@param $oper оператор сравнения
*@param $val значение для сравнения
*выбросит исключение если при записи строка запроса будет пустой
*/
    public function where($what,$oper,$val)
	{
	    if (empty($this->record)){
		    throw new MyExceptionMethod ('Невозможно записать WHERE');
		}else{
			$this->record = $this->record .' WHERE ';
			$this->strWhere($what, $oper, $val); 
		}
	    return $this;
	}
/**
*orWhere формирование строки запроса с использованием ключевого слова WHERE и оператора OR
*@param $what имя поля
*@param $oper оператор сравнения
*@param $val знаение для сравнения
*$b array "белый список" операторов сравнения
*выбросит исключение без использования метода where
*/
    public function orWhere($what,$oper,$val)
	{
	    if (!strstr ($this->record, 'WHERE')){
		    throw new MyExceptionMethod ('Отсутствует WHERE');
		} else{
			$this->record = $this->record .' OR ';
			$this->strWhere($what, $oper, $val); 
		}
	    return $this;
	}
/**
*andWhere формирование строки запроса с использованием ключевого слова WHERE и оператора AND
*@param $what имя поля
*@param $oper оператор сравнения
*@param $val знаение для сравнения
*выбросит исключение без использования метода where
*/
	public function andWhere($what,$oper,$val)
	{
	    if (!strstr ($this->record, 'WHERE')){
		    throw new MyExceptionMethod ('Отсутствует WHERE');
		} else{
			$this->record = $this->record .' AND ';
			$this->strWhere($what, $oper, $val); 
		}
	return $this;
	}
/**
*strWhere формирование строки запроса для ключевого слова WHERE с различными операторами
*@param $what имя поля
*@param $oper оператор сравнения
*@param $val знаение для сравнения
*$b array "белый список" операторов сравнения
*выбросит исключение без использования одного из методов: where, andWhere, orWhere
*/	
    private function strWhere ($what, $oper, $val)
    {
		 if (!strstr ($this->record, 'WHERE')){
		    throw new MyExceptionMethod ('Отсутствует WHERE');
		} else{
	        $b = array ("=", ">", "<", ">=", "<=", "<>", "LIKE", "IN", "NOT IN", "BETWEEN", "IS NULL");
		        $key = array_search ($oper, $b);
		        $oper = $b[$key];
	        if (($oper == 'IN')||($oper == 'NOT IN')){
		        $this->val_array=array_values($val);
		        $this->record = $this->record .$what .' ' .$oper .' ' .'(';
			        foreach ($val as $value){
				        $this->record = $this->record .'?'.', ';
                    }
		        $this->record = substr($this->record, 0, -2).')';
	        } elseif($oper == 'BETWEEN'){
                $this->val_array=array_values($val);
		        $this->record = $this->record .$what .' ' .$oper .' ' .'?' .' ' .'AND' .' ' .'?';
	        } elseif($oper == "IS NULL"){
		        $this->record = $this->record .$what .' ' .$oper;
	        } else{
		        $this->val_array[]= $val;
		        $this->record = $this->record .$what .' '.$oper.' '.'?';
	        }
        }			
	    return $this;
    }	
/**
*limit формирование строки запроса с ключевым словом LIMIT
*@param limit int лимит запроса
*выбросит исключение если при записи строка запроса будет пустой 
*/
    public function limit($limit)
	{
	    if (empty($this->record)){
		    throw new MyExceptionMethod ('Невозможно записать LIMIT');
		} else{
		    $limit = intval($limit);
		    $this->val_array[]= $limit;
		    $this->record = $this->record .' LIMIT ' .'?';
		}
	    return $this;
	}
/**
*offset формирование строки запроса с ключевым словом OFFSET
*@param offset int значение смещения
*выбросит исключение без использования метода limit
*/
    public function offset ($offset)
	{
	    if (!strstr($this->record, 'LIMIT')){
		    throw new MyExceptionMethod ('Отсутствует LIMIT');
		} else{
		    $offset = intval ($offset);
		    $this->val_array[]= $offset;
		    $this->record = $this->record .' OFFSET ' .'?';
		}
	    return $this;
	}
/**
*order формирование строки запроса с ключевым словом ORDER
*@param $columns поля для сортировки
*@param $por порядок сортировки, если не указан то по умолчанию ASC 
*$orders array "белый список" значений сортировки
*/
    public function order ($columns, $por)
	{
	    if (empty($this->record)){
		    throw new MyExceptionMethod ('Невозможно записать ORDER');
		} else{
		    $orders = array("ASC","DESC");
		    $key = array_search ($por, $orders);
		    $orderby = $orders[$key];
			    if (is_array($columns)){
				    if (count ($columns)>1){
					    $this->record = $this->record .' ORDER BY ' . implode (', ',$columns) .' ' .$orderby;
				    }
				} else{
				    $this->record = $this->record .' ORDER BY ' .$columns .' ' .$orderby;
				}
		}
	    return $this;
	}
/**
*selectJoin формирование строки запроса с ключевым словом SELECT, используя объединение таблиц JOIN
*@param $what ассоциативный массив, $key - таблица, $value - значение выборки
*/	
	public function selectJoin($what)
	{
		$this->record = 'SELECT ';
		    foreach ($what as $key=>$value){
		        $this->record = $this->record .$key .'.' .$value .', ';
		    }
		$this->record = substr($this->record, 0, -2);
		return $this;
	}
/**
*fromJoin формирование строки запроса с ключевым словом FROM, используя объединение таблиц JOIN
*@param $tables массив, содержащий ДВЕ таблицы для объединения
*@param $join содержит ключевое слово условия объединения
* $joining "белый список" ключевых слов для объединения
* $tmp_line временная вспомогательная строка
*/
	public function fromJoin($tables, $join)
	{
		if (empty($this->record)){
		    throw new MyExceptionMethod ('Невозможно записать JOIN');
		} else{
		    $joining = array ("INNER", "LEFT", "RIGHT", "FULL", "CROSS");
		        $key = array_search ($join, $joining);
		        $join = $joining[$key];
		    $this->record = $this->record .' FROM ';
		        $tmp_line = $tmp_line .' '.$join .' JOIN ';
		    $tables = array_slice( $tables, 0, 2 );
		    $this->record = $this->record . implode ($tmp_line ,$tables);
		}
		return $this;
	}
/**
*addJoin добавление новой таблицы в строку запроса с JOIN 
*@param $table таблица для объединения
*@param $join содержит ключевое слово условия объединения
* $joining "белый список" ключевых слов для объединения
*/	
	public function addJoin($join, $table)
	{
		if (!strstr($this->record, 'FROM')){
		    throw new MyExceptionMethod ('Отсутствует FROM');
		} else{
		$joining = array ("INNER", "LEFT", "RIGHT", "FULL", "CROSS");
		    $key = array_search ($join, $joining);
		    $join = $joining[$key];
		$this->record = $this->record .' '.$join .' JOIN ' .' ' .$table;	
		}
		return $this;
	}
/**
*onJoin формирование строки запроса, используя объединение таблиц JOIN и параметр ON
*@param $val ассоциативный массив, $key - таблица, $value - значение для сравнения
*@param $oper оператор сравнения 
* $operators "белый список" операторов сравнения
*/
	public function onJoin($val, $oper)
	{
		if (!strstr($this->record, 'JOIN')){
		    throw new MyExceptionMethod ('Отсутствует JOIN');
		} else{
		    $operators = array ("=", ">", "<", ">=", "<=", "<>");
		        $key = array_search ($oper, $operators);
		        $oper = $operators[$key];
		    $this->record = $this->record .' ON ';
            $this->val_array = array_values($val);
		        foreach ($val as $key=>$value){
		            $this->record = $this->record .$key .'.' .'?' .' ' .$oper .' ';
		        }
		    $this->record = substr($this->record, 0, -2);
		}
		return $this;
	}
/**
*usingJoin формирование строки запроса, используя объединение таблиц JOIN и параметр USING
*@param $value значение для сравнения
*/
	public function usingJoin($value)
	{
		if (!strstr($this->record, 'JOIN')){
		    throw new MyExceptionMethod ('Отсутствует JOIN');
		} else{
            $this->val_array = $value;
		    $this->record = $this->record .' USING ' .'?';
		}
		return $this;
	}
/**
*query выполнение запроса с подготовленными выражениями
*/	
    public function query()
	{
	    $this->sth = $this->db->prepare($this->record);
	    $this->sth->execute($this->val_array);
		if (!isset($this->sth)){
			throw new MyExceptionQuery ('Не удалось выполнить запрос');
		}
	    return $this;
	}
/**
*getArray получение результа запроса в виде массива
*/	
    public function getArray()
	{
	    $result = $this->sth->fetchAll();
		if (!isset($result)){
			throw new MyExceptionQuery ('Не удалось получить результат запроса');
		}
	    print_r($result);
	}
/**
*getRow получение результа запроса в виде строки из массива
*/	
    public function getRow()
	{
		$result = $this->sth->fetch(PDO::FETCH_ASSOC);
		if (!isset($result)){
			throw new MyExceptionQuery ('Не удалось получить результат запроса');
		}
	    print_r($result);
	}
/**
*getCountRow получение результа запроса в виде количества затронутых строк
*/	
	public function getCountRow()
	{
		if (isset($this->count_row)){
			$count = $this->count_row;
		} else{
			$count = $this->sth->rowCount();
		}
		if (!isset($count)){
			throw new MyExceptionQuery ('Не удалось получить результат запроса');
		}
		print('Количество затронутых строк: '. $count);
	}
/**
*rawQuery выполнение запроса, построенного самостоятельно
*@param $line_sql содержит строку запроса
*/	
    public function rawQuery($line_sql)
	{
	    if (strstr($line_sql,'SELECT')){
		    $this->sth = $this->db->query($line_sql);
		} else{
			$this->count_row = $this->db->exec($line_sql);
        }
		if ((!isset($this->count_row))||(!isset($this->sth))){
			throw new MyExceptionQuery ('Не удалось выполнить запрос');
		}
	    return $this;
	}
}
?>