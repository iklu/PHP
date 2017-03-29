<?php
ini_set('memory_limit','4000M');
require "ArrayClass.php";

//$eFlags = new FlagsEnum("HAS_ADMIN", "HAS_SUPER", "HAS_POWER", "HAS_GUEST");

function cache_set($key, $val) {
   $val = var_export($val, true);
   // HHVM fails at __set_state, so just use object cast for now
   $val = str_replace('stdClass::__set_state', '(object)', $val);
   // Write to temp file first to ensure atomicity
   $tmp = "tmp/$key." . uniqid('', true) . '.tmp';
   file_put_contents($tmp, '<?php $val = ' . $val . ';', LOCK_EX);
   rename($tmp, "tmp/$key");
}



//generate data
$data = array_fill(0, 1000000, str_shuffle("ovidiusdfsdf"));
foreach($data as $value) {
	$arrayOfData[] = new ArrayClass($value);
}
$data  =  $arrayOfData;

//set cache
cache_set('my_key', $data);

//set redis
$service = new \Redis();
$service->connect("127.0.0.1", "6379");
$service->set('my_key', serialize($data));