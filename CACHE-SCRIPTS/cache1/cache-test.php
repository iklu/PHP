<?php
ini_set('memory_limit','4000M');
require "ArrayClass.php";

function cache_get($key) {
    
    @include "tmp/$key";

    if(isset($val)) {
        return $val;
    } else {
        return false;
    }
}

$service = new \Redis();
$service->connect("127.0.0.1", "6379");

$starttime = microtime(true);
$data = cache_get('my_key');



eval('$datas ='.var_export($data, true).';' );

print_r($datas);

// foreach($data as $value) {
//     var_dump($value);
// }



$difftime = microtime(true)-$starttime;
echo $difftime."\n";

echo "<br>";
$starttime = microtime(true);
$data = $service->get("my_key");
var_dump($data);
$difftime = microtime(true)-$starttime;
echo $difftime."\n";
