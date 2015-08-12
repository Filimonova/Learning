<?php
require_once ("functions.php");

    try{ 
        $obj = new MyQueryBuilder;
	} catch (MyExceptionConnect $c){
	    echo 'Возникло исключение при подключении к базе данных';
	} catch (Exception $e){
	    echo 'Возникло исключение другого типа',  $e->getMessage();
	} 

    try{ 
        $obj->select('name')->from('users')->where('age','<',40);
	} catch (MyExceptionMethod $n){
	    echo 'Возникло исключение при формировании строки запроса';
	} catch (Exception $e){
	    echo 'Возникло исключение другого типа',  $e->getMessage();
	} 
	
	try{
		$obj->query();
	} catch (MyExceptionQuery $m){
	    echo 'Возникло исключение при выполнении запроса';
	} catch (Exception $e){
	    echo 'Возникло исключение другого типа',  $e->getMessage();
	} 
	
	try{
		$obj->getRow();
	} catch (MyExceptionQuery $m){
	    echo 'Возникло исключение при получении результата запроса';
	} catch (Exception $e){
	    echo 'Возникло исключение другого типа',  $e->getMessage();
	} 
?>