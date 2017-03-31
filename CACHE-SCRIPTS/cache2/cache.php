
<?php

require_once "ArrayClass.php";

ini_set('memory_limit','4000M');
/*
removeHours()
return the date given but with the given number of hours removed
*/
function removeHours($date, $hours_added){
    $total_seconds = 3600*$hours_added;// 3600 seconds in an hour
    $date = strtotime($date);
    $new_date = $date-$total_seconds;
    $new_date = date('Y\-m\-d H\:i\:s',$new_date);
    return $new_date;
}


$service = new \Redis();
$service->connect("127.0.0.1", "6379");

    // cache filename variables
    $cache_filename = 'object_data.inc';
    $cachefile_full_filename = $_SERVER['DOCUMENT_ROOT'].'/CACHE-SCRIPTS/cache2/cache/'.$cache_filename;
     
    // check for cache, if it exists and is less than 1 hour old grab it
    if(file_exists($cachefile_full_filename) && filemtime($cachefile_full_filename) > strtotime(removeHours(date('Y-m-d H:i:s'), 1))){
        echo "<br>";
        $starttime = microtime(true);
        $object_data = unserialize(file_get_contents($cachefile_full_filename));
        $difftime = microtime(true)-$starttime;
        echo $difftime."<br>";
        echo "from cache<br>";
      
    } else {
        //generate data
        $data = array_fill(0, 1000000, str_shuffle("ovidiusdfsdf"));
        foreach($data as $value) {
            $arrayOfData[] = new ArrayClass($value);
        }
        $data  =  $arrayOfData;
        $d = 'cache2';

        file_put_contents($cachefile_full_filename, serialize($data));
        $service->set('cache2', $object_data);
    }







