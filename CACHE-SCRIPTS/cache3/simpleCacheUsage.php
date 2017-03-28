<?php
require "FileCaching.php";
define("DB","./someFolder/");
/********************************************************************/
$file = "json-file.json";
if (($jsonData = getCache(md5($file))) === false){
	$jsonData = setCache(md5($file), json_decode(file_get_contents(DB . $file), true),30);
}

print_r($jsonData);//return cached value
//If not cached, store it and simultaneously return the value
/********************************************************************/

