<?php
require "curl-simple.php";
// Todas url gravadas em array
// $url[] = 'http://192.168.0.152/API/web/api/store/4/';
// $url[] = 'http://192.168.0.152/API/web/api/coupons/?storeId=4';
// $url[] = 'http://192.168.0.152/API/web/api/coupons/?storeId=44';
// // $url[] = 'http://192.168.0.152/API/web/api/dma/?sortField=id&sortType=ASC';
$url[] = 'http://www.meineke.com';
$url[] = 'https://www.maaco.com';


$starttime = microtime(true);

$mh = curl_multi_init();
foreach($url as $key => $value){
  $ch[$key] = curl_init($value);
  curl_setopt($ch[$key], CURLOPT_NOBODY, false);
  curl_setopt($ch[$key], CURLOPT_HEADER, false);
  curl_setopt($ch[$key], CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch[$key], CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch[$key], CURLOPT_SSL_VERIFYHOST, false); 
  curl_multi_add_handle($mh,$ch[$key]);
}

//Execute
do {
  curl_multi_exec($mh, $running);
  curl_multi_select($mh);
} while ($running > 0);

//Get Responses
foreach(array_keys($ch) as $key => $value){
  $res[$key]["status"] =  curl_getinfo($ch[$value], CURLINFO_HTTP_CODE);
  $res[$key]["response"] =  json_decode(curl_multi_getcontent($ch[$value]), true); 
  curl_multi_remove_handle($mh, $ch[$value]);
  curl_close($ch[$value]);
}

curl_multi_close($mh);

//print_r($res);

$difftime = microtime(true)-$starttime;
echo $difftime;
echo "\n";

$starttime2 = microtime(true);

curl('http://www.meineke.com');
curl('https://www.maaco.com');

$difftime2 = microtime(true)-$starttime2;
echo $difftime2;

