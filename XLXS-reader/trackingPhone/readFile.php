<?php

date_default_timezone_set('America/Los_Angeles');
require_once '../xlsx/Classes/PHPExcel.php';
include '../xlsx/Classes/PHPExcel/IOFactory.php';

$mainFile = 'Marchex Organic Call Tracking Test List v1 - for Xivic.xlsx';



	$inputFileType = PHPExcel_IOFactory::identify($mainFile);
	$objReader = PHPExcel_IOFactory::createReader($inputFileType);


	$objPHPExcel = $objReader->load($mainFile);
	$sheet = $objPHPExcel->getSheet(0);
	$highestRow = $sheet->getHighestRow();
	$highestColumn = $sheet->getHighestColumn();
	$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
	$headings = $sheet->rangeToArray('A1:' . $highestColumn . 1, NULL, TRUE, FALSE);

	for ($row = 2; $row <= $highestRow; ++ $row) {
		 //  Read a row of data into an array
	                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
	                $rowData[0] = array_combine($headings[0], $rowData[0]);
	                echo "<pre>";
	                $data[] = $rowData[0];



	                //Insert into database
	 //    $val=array();
		// for ($col = 0; $col < $highestColumnIndex; ++ $col) {
		//    $cell = $worksheet->getCellByColumnAndRow($col, $row);
		//    $val[] = $cell->getValue();
		//  //End of For loop   
		// }

		// $Col1 = $val[0] ;
		// $Col2 = $val[1] ;
		// $Col3 = $val[2];

		// echo $Col1;
		// echo $Col2;
		// echo $Col3;
		// echo "<br>";

		// //End of for loop
	}



	$fp = fopen('organic_dni_ct_phone.csv', 'w');

	    fputcsv($fp, array_keys($data[0]));
	    foreach($data as $fields) {
	      fputcsv($fp, $fields);
	    }

	    fclose($fp);
		print_r($data);

?>

