<?php

class Curl 
{

    public $link;
    public $parameters;
    public $method;
    public $header;
    public $file;

    public function __construct($link, $parameters=array(), $method="GET" , $headers=array() , $file="") {
        $this->link=$link;
        $this->parameters=$parameters;
        $this->method= $method;
        $this->headers = $headers;
        $this->file=$file;

    }

    /**
     * @param $link
     * @param $parameters
     * @param string $method
     */
    public  function executeCurl()
    {
         $fp = fopen ('csv-diff.csv', 'w+');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        //curl_setopt($curl, CURLOPT_URL, 'https://meineke'. trim($request->get('storeId')) . '.fullslate.com/api/bookings');
        curl_setopt($ch, CURLOPT_URL, $this->link);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if($this->headers != NULL){
            curl_setopt($ch,CURLOPT_HTTPHEADER,array($this->headers));
        }
        if(($this->method == 'POST' or $this->method == 'PUT') AND $this->parameters != NULL){
            if($ch){
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->parameters);               
                curl_setopt($ch, CURLOPT_FILE, $fp); 
            }else{
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->parameters));
            }
        } else {
               curl_setopt($ch, CURLOPT_FILE, $fp); 
        }


//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);


        $response = curl_exec($ch);

        $info = curl_getinfo($ch)["http_code"];
        curl_close($ch);
        fclose($fp);

        $arr = array();
        $arr['status'] = $info;
        $arr['response'] = $response;

        return $arr;

    }
}
