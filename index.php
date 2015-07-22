<?php
require_once ("functions.php");
$obj = new MyQueryBuilder;

$a = array('name', 'age');

$obj->select($a)->from('users')->where('city','=','Ульяновск');
$obj->query();
?>