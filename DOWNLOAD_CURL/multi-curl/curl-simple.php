<?php

   function curl($link, $port = NULL, $parameters = NULL, $method = 'GET', $headers = NULL, $file = NULL)
    {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $link);
        if($port){
            curl_setopt($curl, CURLOPT_PORT, $port);
        }
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if($headers != NULL){
            if(is_array($headers)) {
                curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
            } else {
                curl_setopt($curl,CURLOPT_HTTPHEADER, array($headers));
            }
        }
        if(($method == 'POST' or $method == 'PUT') AND $parameters != NULL){
            if($file){
                curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
            }else{
                if(is_array($parameters)) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($parameters));
                    //curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($parameters));
                } else {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
                }
            }
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        $response = curl_exec($curl);

        $info = curl_getinfo($curl)["http_code"];

        $arr = array();
        $arr['status'] = $info;
        $arr['response'] = json_decode($response, true);

      
        return $arr;
    }
