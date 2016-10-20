<?php

/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 11.10.2016
 * Time: 11:57
 */
namespace Acme\DataBundle\Model\ClutchService\Account;

class ClutchAccount
{
    protected $vehicleCardNumber;

    protected $customerCardNumber;

    protected $container;

    /**
     * ClutchAccount constructor.
     * @param $container
     */
    public function __construct($container) {
        $this->container = $container;
    }

    public function searchByVehicleCardNumber($cardNumber){
        $vehicleData = $this->getClutchService()->getVehicleInfo($cardNumber);
        return $this->vehicleCardNumber = $this->setVehicleCardNumber($vehicleData);
    }

    public function searchByCustomerData($email, $phone){
        $customerData = $this->getClutchService()->getCustomerInfo($email, $phone);
        return $this->customerCardNumber = $this->setCustomerCardNumber($customerData);
    }
    
    public function getClutchService() {
        return  $this->container->get("meineke.clutch_service");
    }

    public function getEntityManager() {
        return  $this->container->get("doctrine.orm.entity_manager");
    }

    public function getCache() {
        return  $this->container->get('cacheManagementBundle.redis');
    }

    public function setVehicleCardNumber($entity){

        $cardNumber = new CardNumber();
        $cardNumber->setBrandDemographics(isset($entity["brandDemographics"])?$entity["brandDemographics"]:"");
        $cardNumber->setMailings(isset($entity["mailings"])?$entity["mailings"]:"");

        return $cardNumber;
    }

    public function getVehicleCardNumber() {
        return $this->vehicleCardNumber ;
    }

    public function setCustomerCardNumber($entity){

        $cardNumber = new CardNumber();

        $cardNumber->setCardNumber(isset($entity["cardNumber"])?$entity["cardNumber"]:"");
        $cardNumber->setCustomCardNumber(isset($entity["customCardNumber"])?$entity["customCardNumber"]:"");
        $cardNumber->setBalance(isset($entity["balance"])?$entity["balance"]:"");
        $cardNumber->setFirstName(isset($entity["firstName"])?$entity["firstName"]:"");
        $cardNumber->setLastName(isset($entity["lastName"])?$entity["lastName"]:"");
        $cardNumber->setEmail(isset($entity["email"])?$entity["email"]:"");
        $cardNumber->setPhone(isset($entity["phone"])?$entity["phone"]:"");
        $cardNumber->setBrandDemographics(isset($entity["brandDemographics"])?$entity["brandDemographics"]:"");

        return $cardNumber;
    }

    public function getCustomerCardNumber() {
        return $this->customerCardNumber ;
    }

}