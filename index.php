<?php
require_once ("functions.php");
$obj = new MyQueryBuilder;

$obj->select('name')->from('users')->where('id','=',6);
$obj->query()->getResult();
?>