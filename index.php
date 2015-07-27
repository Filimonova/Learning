<?php
require_once ("functions.php");

$obj = new MyQueryBuilder;
	
	try{ 
	$obj->select('name')->from('users')->where('id','=',4);
	 }
	catch (MyQueryBuilderException $m){
		echo 'Возникла ошибка при формировании строки запроса',$m->file_exception_method();
	}
	catch (Exception $e){
		echo 'Возникла ошибка другого типа',  $e->getMessage();
	} 
	$obj->query()->getResult();
	
?>