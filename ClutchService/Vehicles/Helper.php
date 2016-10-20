<?php

/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 11.10.2016
 * Time: 11:15
 */
namespace Acme\DataBundle\Model\ClutchService\Vehicles;

class Helper
{
    public static function cleanVehicleId($vehicleId){

        $trim = trim($vehicleId);
        $vehicle = "veh_".$trim;

        return $vehicle;

    }

    public static function explodeVehiclesIds($vehicleIds){
        $vehicles = explode(",", $vehicleIds);
        return $vehicles;
    }
}