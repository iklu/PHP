<?php

namespace Acme\DataBundle\Model\FullSlateService;

use Acme\DataBundle\Model\Utility\Curl;
use Symfony\Component\HttpFoundation\Request;

class FullSlate
{

    public static function checkFullSlate($id, $fullslateUrl)
    {
        strpos($fullslateUrl, '{id}') ? $url = str_replace('{id}', $id, $fullslateUrl) : $url = $fullslateUrl;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

    public static function getFullSlateServices($id, $fullslateUrl)
    {
        strpos($fullslateUrl, '{id}') ? $url = str_replace('{id}', $id, $fullslateUrl) : $url = $fullslateUrl;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url . '/services');
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

    public static function getFullSlateOpenings($id, $services, $sampling = false, $fullslateUrl)
    {
        strpos($fullslateUrl, '{id}') ? $url = str_replace('{id}', $id, $fullslateUrl) : $url = $fullslateUrl;

        $before = date("Ymd", strtotime('+30 days'));

        //build services query for Full Slate
        $allServices = explode(",", $services);
        if (count($allServices) === 1) {
            $data = "service=" . urlencode($allServices[0]);
        } else {
            $data = '';
            foreach ($allServices as $value) {
                $data .= "services[]=" . urlencode($value) . "&";
            }
            $data = rtrim($data, "& ");
        }

        $curl = curl_init();
        if ($sampling) {
            curl_setopt($curl, CURLOPT_URL, $url . '/openings?' . $data);
        } else {
            curl_setopt($curl, CURLOPT_URL, $url . '/openings?' . $data . '&before=' . $before);
        }
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

    public static function saveFullSlateAppointment($storeId, $postValues, $params) {
        $url = Helper::parseURL($storeId, $params["fullslate_url"]);
        $booking = Curl::curl($url . "/bookings?app=" . $params['fullslate_security_key'], "", $postValues, "POST");
        return $booking;
    }


    public static function checkFullSlateBooking($storeId, $bookingId, $params) {
        $url = Helper::parseURL($storeId, $params["fullslate_url"]);
        $booking = Curl::curl($url . "/bookings/" . $bookingId);
        return $booking;
    }

    public static function deleteFullSlateBooking($storeId, $bookingId, $params) {
        $url = Helper::parseURL($storeId, $params["fullslate_url"]);
        $booking = Curl::curl($url . "/bookings/" . $bookingId, "", "", "DELETE");
        return $booking;
    }

}
