<?php
include_once('Curl.php');
 ob_start();
$header =[];

$curl = new Curl("http://192.168.0.159/API/web/api/logs/store-csv-diff/?date1=2016-12-03&date2=2016-12-04");
$c  = $curl->executeCurl();

//  print_r($c);
// // exit;

// file_put_contents('data.zip', 'dd');



header('Content-type: text/csv');
header('Content-length: '.filesize('csv-diff.csv'));
header('Content-Disposition: attachment; filename="'.basename('csv-diff.csv').'"');

ob_end_clean();
readfile('csv-diff.csv');
die();

//echo($c['response']);



