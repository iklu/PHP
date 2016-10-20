<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 11.10.2016
 * Time: 14:42
 */

namespace Acme\DataBundle\Model\ClutchService\Vehicles;


use Acme\DataBundle\Model\ClutchService\Account\BrandDemographics;
use Acme\DataBundle\Model\ClutchService\Account\ClutchAccount;



class VehiclesManager
{
    /**
     * @var ClutchAccount
     */
    public $clutchAccount;

    public $container;

    public function __construct(ClutchAccount $clutchAccount, $container)
    {
        $this->clutchAccount = $clutchAccount;
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getAllVehicles(){

        $brandDemographics = new BrandDemographics($this->clutchAccount);
        $brandDemographics->getCustomerBrandDemographics();
        
        $vehicleIds = $brandDemographics->getVehiclesIds();

        if(!empty($vehicleIds)) {
            $vehicleIds = Helper::explodeVehiclesIds($vehicleIds);
        } else {
            $vehicleIds = array();
        }

        $vehicles = array();
        for($i=0;$i<count($vehicleIds);$i++) {
            
            $vehicle = Helper::cleanVehicleId($vehicleIds[$i]);

            /** search by vehicle card number */
            $this->clutchAccount->searchByVehicleCardNumber($vehicle);

            $this->__construct($this->clutchAccount, $this->container);

            $brandDemographics = new BrandDemographics($this->clutchAccount);
            $brandDemographics->getVehicleBrandDemographics();

            $vehicles[$i]['vehicleId'] = $vehicle;
            $vehicles[$i]['make'] = $brandDemographics->getMake();
            $vehicles[$i]['year'] = $brandDemographics->getYear();
            $vehicles[$i]['model'] = $brandDemographics->getModel();
            $vehicles[$i]['vin'] = $brandDemographics->getVin();
            $vehicles[$i]['tag'] = $brandDemographics->getTag();
            $vehicles[$i]['image'] = $brandDemographics->getImage();
            $vehicles[$i]['vehicleNickname'] = $brandDemographics->getVehicleNickname();
            $vehicles[$i]['shortNote'] = $brandDemographics->getVehicleNote();
            $vehicles[$i]['mailings'] = $this->clutchAccount->getVehicleCardNumber()->getMailings();
            $vehicles[$i]['historyTransactions'] = $this->clutchAccount->getClutchService()->getHistoryTransaction($vehicle);

        }

        return $vehicles;
    }
}