<?php
require_once ("functions.php");

$obj = new MyQueryBuilder;
$a = array("age"=>51,
           "city"=>"Самара",
           "pol"=>"женский");
    try{ 
        //$obj->update('users')->set($a)->where('id','=','22');
	}
    catch (MyQueryBuilderException $m){
	    echo 'Возникло исключение при формировании строки запроса',$m->fileExceptionMethod();
	}
    catch (Exception $e){
	    echo 'Возникло исключение другого типа',  $e->getMessage();
	} 
	//$obj->query()->getRow();	
	
/* $sql_upd = 'UPDATE users SET age = 27 WHERE id = 22';
$sql_sel = "SELECT name FROM users WHERE pol = 'женский'";
$obj->rawQuery($sql_upd)->getRow(); */

$what1 = array ("users"=>"name",
               "contacts"=>"phone");
$join1 = "INNER";
$val1 = array ("users"=>"id",
              "contacts"=>"id_user");
$obj->selectJoin($what1, $join1, $val1, '>');
$obj->query()->getArray();
?>