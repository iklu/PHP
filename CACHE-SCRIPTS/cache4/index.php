<?php
require "BinaryCache.php";
require "ArrayClass.php";


ini_set('memory_limit','4000M');
$cache = new BinaryCache();
$cache->init();


//much more fast

$starttime = microtime(true);
$dataFromCache = $cache->retrieve("names");
$difftime = microtime(true)-$starttime;
echo $difftime."<br>";

if(!$dataFromCache) {
    $generate = array_fill(0, 1000000, str_shuffle("ovidiusdfsdf"));
    foreach($generate as $value) {
        $data[] = new ArrayClass($value);
    }
    $cache->store("names", $data);
    echo "Save to cache";
    unset($data);
} else {
    echo "From Cache ";
}

//set redis
$service = new \Redis();
$service->connect("127.0.0.1", "6379");

//slower
$starttime = microtime(true);
$dataFromRedis = $service->get("names");
unserialize($dataFromRedis);
$difftime = microtime(true)-$starttime;
echo "<br>".$difftime."<br>";



if(!$dataFromRedis) {
    $data = array_fill(0, 1000000, str_shuffle("ovidiusdfsdf"));
    foreach($data as $value) {
        $data[] = new ArrayClass($value);
    }
    $service->set("names", serialize($data));
} else {
    echo "From Redis Cache.";
}


//$cache->erase("names");