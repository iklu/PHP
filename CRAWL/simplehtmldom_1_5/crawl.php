<?php
require_once "simple_html_dom.php";
require_once "array.php";

$index = "http://www.timetemperature.com/directory/united-states.html";
$html = file_get_html($index);

$timezones = array(
    array(
        "abbreviation"=>"AST",
        "timezoneName"=>"ATLANTIC STANDARD TIME",
        "UTCOffset"=>"UTC - 4",
    ),   array(
        "abbreviation"=>"EST",
        "timezoneName"=>"EASTERN STANDARD TIME",
        "UTCOffset"=>"UTC - 5",
    ),   array(
        "abbreviation"=>"EDT",
        "timezoneName"=>"EASTERN DAYLIGHT TIME",
        "UTCOffset"=>"UTC - 4",
    ),   array(
        "abbreviation"=>"CST",
        "timezoneName"=>"CENTRAL STANDARD TIME",
        "UTCOffset"=>"UTC - 6",
    ),   array(
        "abbreviation"=>"CDT",
        "timezoneName"=>"CENTRAL DAYLIGHT TIME",
        "UTCOffset"=>"UTC - 5",
    ),   array(
        "abbreviation"=>"MST",
        "timezoneName"=>"MOUNTAIN STANDARD TIME",
        "UTCOffset"=>"UTC - 7",
    ),   array(
        "abbreviation"=>"MDT",
        "timezoneName"=>"MOUNTAIN DAYLIGHT TIME",
        "UTCOffset"=>"UTC - 6",
    ),   array(
        "abbreviation"=>"PST",
        "timezoneName"=>"PACIFIC STANDARD TIME",
        "UTCOffset"=>"UTC - 8",
    ),   array(
        "abbreviation"=>"PDT",
        "timezoneName"=>"PACIFIC DAYLIGHT TIME",
        "UTCOffset"=>"UTC - 7",
    ),   array(
        "abbreviation"=>"AKST",
        "timezoneName"=>"ALASKA TIME",
        "UTCOffset"=>"UTC - 9",
    ),   array(
        "abbreviation"=>"AKDT",
        "timezoneName"=>"ALASKA DAYLIGHT TIME",
        "UTCOffset"=>"UTC - 8",
    ),   array(
        "abbreviation"=>"HST",
        "timezoneName"=>"HAWAII STANDARD TIME",
        "UTCOffset"=>"UTC - 10",
    ),   array(
        "abbreviation"=>"HAST",
        "timezoneName"=>"HAWAII-ALEUTIAN STANDARD TIME",
        "UTCOffset"=>"UTC - 10",
    ),   array(
        "abbreviation"=>"HADT",
        "timezoneName"=>"HAWAII-ALEUTIAN DAYLIGHT TIME",
        "UTCOffset"=>"UTC - 9",
    ),   array(
        "abbreviation"=>"SST",
        "timezoneName"=>"SAMOA STANDARD TIME",
        "UTCOffset"=>"UTC - 11",
    ),   array(
        "abbreviation"=>"SDT",
        "timezoneName"=>"SAMOA DAYLIGHT TIME",
        "UTCOffset"=>"UTC - 10",
    ),   array(
        "abbreviation"=>"CHST",
        "timezoneName"=>"CHAMORRO STANDARD TIME",
        "UTCOffset"=>"UTC + 10",
    ),
);



$i = 0;
$output = fopen('us_timezone.csv', 'w+');
$finalData["STATE"]  =  "STATE";
$finalData["CITY"]  =  "CITY";
$finalData["STANDARD_TZ"]  = "STANDARD_TZ";
$finalData["DAYLIGHT_TZ"] = "DAYLIGHT_TZ";
$finalData["UTC_STANDARD"] = "UTC_STANDARD";
$finalData["UTC_DAYLIGHT"] = "UTC_DAYLIGHT";
$finalData["START_DAYLIGHT"] = "START_DAYLIGHT";
$finalData["END_DAYLIGHT"] = "END_DAYLIGHT";

fputcsv($output, $finalData);

foreach($html->find('a') as $state) {
    if(strpos($state->href, "directory")){
       $cities = file_get_html($state->href);
        foreach($cities->find('a') as $city) {
            if(!strpos($city->href, "_time_zone") && !strpos($city->href, "tz") &&  !strpos($city->href, "directory") && preg_match("#\.\/#", $city->href)){ 
                $citiesPages = file_get_html(substr($state->href, 0, strrpos( $state->href, '/'))."/".$city->href);
                foreach($citiesPages->find('a') as $cityPage) {
                    if(!strpos($cityPage->href, "_time_zone") && strpos($cityPage->href, "tz") &&  !strpos($cityPage->href, "directory")){

                        //write the links were timezones can be found
                        file_put_contents('links.txt',$cityPage->href . PHP_EOL, FILE_APPEND);


                        $ex = explode(",",$cityPage->plaintext);

                        if(isset($ex[1])) {
                            $data["STATE"] = trim($ex[1]);
                        } else {
                            $data["STATE"] = "";
                        }

                        if(isset($ex[0])) {
                            $data["CITY"] = trim($ex[0]);
                        } else {
                            $data["CITY"] = "";
                        }

                        if($data["CITY"]!="" && $data["STATE"]!="") {
                            $tzLink = $cityPage->href;
                            $tzPage = file_get_html($tzLink);

                            $td = $tzPage->find('.contentfont');
                            foreach($td as $row) {
                                $rowData[] = $row->innertext;
                            }

                            echo ".";

                            //Timezone
                            foreach($timezones as $key => $tz){
                                if(preg_match("#".$timezones[$key]["abbreviation"]."#", $rowData[1], $array)) {
                                    $tmz[] = $array[0];
                                }
                            }

                            $data["STANDARD_TZ"] = $tmz[0];
                            $data["DAYLIGHT_TZ"] = $tmz[1];
                  


                            if(preg_match_all("#UTC\ -\ \d+[h]#", $rowData[2], $array2)) {
                                if(isset( $array2[0][0])) {
                                    $data["UTC_STANDARD"] = $array2[0][0];
                                }
                                if(isset( $array2[0][1])) {
                                    $data["UTC_DAYLIGHT"] = $array2[0][1];
                                }
                            }

                            if(preg_match_all("# \d+#", $rowData[4], $array3)){
                                if(isset($array3[0][0]) && isset($array3[0][1])) {

                                    if(preg_match("/(january|february|march|april|may|june|july|august|september|october|november|december)/i",$rowData[4], $m)) {
                                        $data["START_DAYLIGHT"] =$m[0]."-".trim($array3[0][0])."-".trim($array3[0][1]);
                                    }

                                }
                            }

                            preg_match("/(\d{2})\s(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\s(\d{4})\s(\d{2}):(\d{2})/i",$rowData[5], $m);
                            if(preg_match_all("# \d+#", $rowData[5], $array3)){
                                if(isset($array3[0][0]) && isset($array3[0][1])) {

                                    if(preg_match("/(january|february|march|april|may|june|july|august|september|october|november|december)/i",$rowData[5], $m)) {
                                        $data["END_DAYLIGHT"] =$m[0]."-".trim($array3[0][0])."-".trim($array3[0][1]);
                                    }
                                }
                            }
                            fputcsv($output, $data);
                            unset($tmz);
                            unset($rowData);
                        }
                    }
                }
            }
        }
    }
}