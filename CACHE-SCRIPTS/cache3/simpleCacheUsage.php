<?php
require "FileCaching.php";
require "someFolder/ArrayClass.php";
ini_set('memory_limit','4000M');



//set redis
$service = new \Redis();
$service->connect("127.0.0.1", "6379");
$service->set(md5($d), serialize($data));
$d = 'names';

$start = microtime(true);
$cacheData = getCache(md5($d));
//print_r($cacheData);
$stop = microtime(true)-$start;
echo $stop."\n";

$start2 = microtime(true);
$redisData = $service->get(md5($d));
$stop2 = microtime(true)-$start2;
echo $stop2."\n";

if ($cacheData === false){
	//generate data
	$data = array_fill(0, 1000000, str_shuffle("ovidiusdfsdf"));
	foreach($data as $value) {
		$arrayOfData[] = new ArrayClass($value);
	}
	$data  =  $arrayOfData;
	$d = 'names';
	$cacheData = setCache(md5($d), $data);
}


//print_r($redisData);
exit;




define("DB","./someFolder/");
/********************************************************************/
$file = "json-file.json";
if (($jsonData = getCache(md5($file))) === false){
	$jsonData = setCache(md5($file), json_decode(file_get_contents(DB . $file), true),30);
}

print_r($jsonData);//return cached value
//If not cached, store it and simultaneously return the value
/********************************************************************/

