<?php

$array = $fields = array(); $i = 0;
        $handle = @fopen("Participating_Centers.csv", "r");
        if($handle) {
            while(($row = fgetcsv($handle, 4096)) !== FALSE) {
                if(empty($fields)) {
                    $fields = $row;
                    continue;
                }

                foreach($row as $k=>$value) {
                    $array[$i][$fields[$k]] = $value;
                }
                $i++;
            }
            if(!feof($handle)) {
                file_put_contents($file, 'Error: unexpected fgets() fail.' . PHP_EOL, FILE_APPEND);
                exit();
            }
            fclose($handle);
        }
        
$row = $array;

for ($i=0; $i<count($row) ;$i++){

	$dbh = new PDO("mysql:host=mnk-integrated.csm5ogryqccg.us-west-2.rds.amazonaws.com;dbname=mnk", "mnk", "m]T_)M7ekWx4G\q5");
	$sth = $dbh->query ("UPDATE stores SET hasVeterans=1 WHERE storeId='".$row[$i]["storeid"]."' ");	
}
