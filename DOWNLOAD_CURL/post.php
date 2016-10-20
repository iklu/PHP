<?php
include_once('Curl.php');
include_once('WSSE.php');

 ob_start();
$email = 'summerlinnunn@gmail.com';
$token = '2r5IIioCZ8uTHAB9ag7ZhfAJVJ4Qjd0Cn0sKjIlppt9NGyqhDKeCzjWHROTAcSyZUMkZwOeMTot3+vTjkTC8jQ==';

$services = "Schimb de Ulei, Brake change";
$date = "04/28/16 12:30 AM";
$car = "Dacia Logan";
$address = "Romania Iasi ";
$serial = "1234";
$info = "Please come on time.";
$phone = "(800) 555-0199";
$terms = "O Fortuna velut luna statu variabilis, semper crescis aut decrescis; vita detestabilis nunc obdurat et tunc curat ludo mentis aciem, egestatem, potestatem dissolvit ut glaciem.\n\n Sors immanis et inanis, rota tu volubilis, status malus, vana salus semper dissolubilis, obumbrata et velata michi quoque niteris; nunc per ludum dorsum nudum fero tui sceleris.\n\n Sors salutis et virtutis michi nunc contraria, est affectus et defectus semper in angaria.  Hac in hora sine mora corde pulsum tangite; quod per sortem sternit fortem, mecum omnes plangite!";
$keytagId = 'dfasdfasf';

$postValues = array(
            "services" => $services,
            "date" => $date,
            "car" => $car,
            "address" => $address,
            "serial" => $serial,
            "info" => $info,
            "phone" => $phone,
            "terms" => $terms, 
            "keytagId"=>$keytagId
        );

$header = WSSE::generateWsse($token, $email);

$curl = new Curl("http://api.meineke-redesign.beta-directory.com/api/secured/generate-passbook/", $postValues, "POST", $header, null);
$c  = $curl->executeCurl();

// print_r($c);
// exit;

// file_put_contents('data.zip', 'dd');

header('Content-type: application/vnd.apple.pkpass');
header('Content-length: '.filesize('localfile.tmp'));
header('Content-Disposition: attachment; filename="'.basename('passbook.pkpass').'"');

ob_end_clean();
readfile('localfile.tmp');
die();

//echo($c['response']);



