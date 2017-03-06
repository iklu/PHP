<?php

function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}

echo convert(memory_get_usage(true))."<br>"; // 123 kb


require_once "simple_html_dom.php";
// set error level
$internalErrors = libxml_use_internal_errors(true);

$index = $_GET['url'];
$html = file_get_html($index);

$host = "http://dev.meineke-redesign.beta-directory.com";

//echo $html->find('form', 3)->innertext;
$i=0;

foreach($html->find('form') as $form) {
    if(!strpos($form->action, 'login') && !strpos($form->action, 'search')){
        if($form->action != '') {
            $forms = '';
            $forms .= "<form action='".$host.$form->action."'  method='POST'><br>";
            $forms .=  $form->innertext;
            $forms .= "<form><br>";
            file_put_contents('attack'.$i++.'.html', $forms);
            
            
            $document = new \DOMDocument();
            $document->loadHTML($form->innertext);

            $inputs = $document->getElementsByTagName("input");
            
            
            echo "FORM PAGE :". $index."<br>";
            echo "FORM: action ". $host.$form->action."<br>";
            foreach ($inputs as $input) {
                echo "input name: ". $input->getAttribute("name")."<br>";
                echo "input value: ". $input->getAttribute("value")."<br>";
                echo "input required: ". $input->getAttribute("required")."<br>";
                echo "input placeholder: ". $input->getAttribute("placeholder")."<br>";
                echo "<hr>";
                if ($input->getAttribute("name") == "id") {
                    $value = $input->getAttribute("value");
                }
            }

            
        }
      
           
    // echo  $form->id.PHP_EOL;
    // echo  $form->action.PHP_EOL;
    // echo  $form->name.PHP_EOL;
    
    
    }
    
    

}
  // Restore error level
libxml_use_internal_errors($internalErrors);


echo convert(memory_get_usage(true));

// foreach($html->find('input') as $input) {
//     echo  $input->name.'<br />';
// }
       
// foreach($html->find('button') as $button) {
//     echo  $button->class.'<br />';
// }

// print_r($states);

// foreach($states as $key=>$value) {
//     $clean = strtolower($value);
    
//     // Create DOM from URL or file
//     $html = file_get_html('http://www.google.com/');
// }

// // Create DOM from URL or file
// $html = file_get_html('http://www.google.com/');









// // Find all images
// foreach($html->find('img') as $element)
//        echo $element->src . '<br>';

// // Find all links
// foreach($html->find('a') as $element)
//        echo $element->href . '<br>'; 