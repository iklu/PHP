<?php
// LICENSE: PUBLIC DOMAIN
// The author disclaims copyright to this source code.
// AUTHOR: Shailesh N. Humbad
// SOURCE: http://www.somacon.com/p539.php
// DATE: 6/4/2008

// index.php
// Run the parallel get and print the total time
$s = microtime(true);
// Define the URLs
// Todas url gravadas em array
$urls[] = 'http://192.168.0.152/API/web/api/store/4/';
$urls[] = 'http://192.168.0.152/API/web/api/coupons/?storeId=4';
$urls[] = 'http://192.168.0.152/API/web/api/dma/?sortField=id&sortType=ASC';

$urls[] = 'http://www.meineke.com';

$urls[] = 'https://www.maaco.com';

$pg = new ParallelGet($urls);
print "<br />total time: ".round(microtime(true) - $s, 4)." seconds";

// Class to run parallel GET requests and return the transfer
class ParallelGet
{
  function __construct($urls)
  {
    // Create get requests for each URL
    $mh = curl_multi_init();
    foreach($urls as $i => $url)
    {
      $ch[$i] = curl_init($url);
      curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
      curl_multi_add_handle($mh, $ch[$i]);
    }

    // Start performing the request
    do {
        $execReturnValue = curl_multi_exec($mh, $runningHandles);
    } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);
    // Loop and continue processing the request
    while ($runningHandles && $execReturnValue == CURLM_OK) {
      // Wait forever for network
      $numberReady = curl_multi_select($mh);
      if ($numberReady != -1) {
        // Pull in any new data, or at least handle timeouts
        do {
          $execReturnValue = curl_multi_exec($mh, $runningHandles);
        } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);
      }
    }

    // Check for any errors
    if ($execReturnValue != CURLM_OK) {
      trigger_error("Curl multi read error $execReturnValue\n", E_USER_WARNING);
    }

    // Extract the content
    foreach($urls as $i => $url)
    {
      // Check for errors
      $curlError = curl_error($ch[$i]);
      if($curlError == "") {
        $res[$i] = curl_multi_getcontent($ch[$i]);
      } else {
        print "Curl error on handle $i: $curlError\n";
      }
      // Remove and close the handle
      curl_multi_remove_handle($mh, $ch[$i]);
      curl_close($ch[$i]);
    }
    // Clean up the curl_multi handle
    curl_multi_close($mh);
    
    // Print the response data
    print_r($res);
  }

}
?>