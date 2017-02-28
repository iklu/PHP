<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 20.02.2017
 * Time: 09:34
 */

namespace Acme\DataBundle\Model\FullSlateService;


use Acme\DataBundle\Entity\Stores;
use Symfony\Component\HttpFoundation\Request;

class Helper
{
    public static function parseURL($storeId, $fullSlateURL){
        strpos($fullSlateURL, '{id}') ? $url = str_replace('{id}', $storeId, $fullSlateURL): $url = $fullSlateURL;
        return $url;
    }

    public static function explode($string){
        $explode = explode(",", trim($string));
        $values = [];
        if (count($explode) === 1) {
            $values = $explode[0];
        } else {
            foreach ($explode as $value) {
                $values[] = $value;
            }
        }
        return $values;
    }

    public static function postValues(Request $request, Stores $store) {

        //get timezone from DB
        $timezone = $store->getTimezone() ? $store->getTimezone() : 'PDT';

        $date = new \DateTime(trim($request->get('dateTime')), new \DateTimeZone($timezone));
        $date->setTimezone(new \DateTimeZone('UTC'));

        $postValues = array(
            "at" => $date->format('Ymd') . 'T' . $date->format('His') . 'Z',
            "first_name" => trim($request->get('firstName')),
            "last_name" => trim($request->get('lastName')),
            "email" => trim($request->get('email')),
            "phone_number" => trim($request->get('phone')),
            "custom-Vehicle Make" => trim($request->get('vehicleMake')),
            "custom-Vehicle Model" => trim($request->get('vehicleModel')),
            "custom-Vehicle Year" => trim($request->get('vehicleYear')),
            "notes" => trim($request->get('comments')),
            //Update 12/19/16: wrap 'paid' in 'api_options' per Fullslate request.
            //"paid" => trim($request->get('paid'))
            "api_options" => "{\"paid\": true}"
        );

        $services = Helper::explode($request->get("services"));

        if (is_array($services)) {
            $postValues["services"] = $services;
        } else {
            $postValues["service"] = $services;
        }

        if (trim($request->get('vehicleDropoff')))
            $postValues["custom-Will you be dropping your vehicle off for service?"] = "on";
        if (trim($request->get('waitForCar')))
            $postValues["custom-Will you be waiting while your car is serviced?"] = "on";
        if (trim($request->get('textReminderSMS')))
            $postValues["sms_reminder_optin"] = "on";
        return $postValues;
    }
    
}