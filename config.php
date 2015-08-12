<?php
/**
*константы для подключения к бд
*/
define ("DRIVER","mysql");
define ("HOST","localhost");
define ("USER","user1");
define ("PASS","12345");
define ("DB_NAME","example");
 /**
 *константы, содержащие названия файлов для записи исключений
 */
define ("METHOD_EXCEPTION_FILE","\\exception_method.txt");
define ("QUERY_EXCEPTION_FILE","\\exception_query.txt");
define ("CONNECT_EXCEPTION_FILE","\\exception_connect.txt");
/**
*константы, разрешающие запись в файл
*значение 1 запись разрешена
*значение 0 запись запрещена
*/
define ("WRITE_METHOD_EXCEPTION_FILE","1");
define ("WRITE_QUERY_EXCEPTION_FILE","1");
define ("WRITE_CONNECT_EXCEPTION_FILE","1");
?>