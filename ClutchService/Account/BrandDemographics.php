<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 11.10.2016
 * Time: 15:56
 */

namespace Acme\DataBundle\Model\ClutchService\Account;

/**
 * Class BrandDemographics
 * @package Acme\DataBundle\Model\ClutchService\Account
 */
class BrandDemographics
{
    /**
     * @var ClutchAccount
     */
    public $clutchAccount;

    protected $brandDemographicsContent;

    /**
     * BrandDemographics constructor.
     * @param ClutchAccount $clutchAccount
     */
    public function __construct(ClutchAccount $clutchAccount) {
        $this->clutchAccount = $clutchAccount;
    }

    public function getCustomerBrandDemographics(){
        $this->brandDemographicsContent = $this->clutchAccount->getCustomerCardNumber()->getBrandDemographics();
    }

    public function getVehicleBrandDemographics(){
        $this->brandDemographicsContent = $this->clutchAccount->getVehicleCardNumber()->getBrandDemographics();
    }

    /**
     * @return string
     */
    public function getVehiclesIds() {
        return  isset($this->brandDemographicsContent['vehicleIds'])?$this->brandDemographicsContent ['vehicleIds']:"";
    }

    /**
     * @return string
     */
    public function getVehicleId(){
        return  isset($this->brandDemographicsContent['vehicleId'])?$this->brandDemographicsContent ['vehicleId']:"";
    }

    /**
     * @return string
     */
    public function getMake(){
        return  isset($this->brandDemographicsContent['vehicle1make'])?$this->brandDemographicsContent ['vehicle1make']:"";
    }

    /**
     * @return string
     */
    public function getYear(){
        return  isset($this->brandDemographicsContent['vehicle1year'])?$this->brandDemographicsContent ['vehicle1year']:"";
    }

    /**
     * @return string
     */
    public function getModel(){
        return  isset($this->brandDemographicsContent['vehicle1model'])?$this->brandDemographicsContent ['vehicle1model']:"";
    }

    /**
     * @return string
     */
    public function getVin(){
        return  isset($this->brandDemographicsContent['vehicle1vin'])?$this->brandDemographicsContent ['vehicle1vin']:"";
    }

    /**
     * @return string
     */
    public function getTag(){
        return  isset($this->brandDemographicsContent['vehicle1tag'])?$this->brandDemographicsContent ['vehicle1tag']:"";
    }

    /**
     * @return string
     */
    public function getImage(){
        return  isset($this->brandDemographicsContent['image'])?$this->brandDemographicsContent ['image']:"";
    }

    /**
     * @return string
     */
    public function getVehicleNickname(){
        return  isset($this->brandDemographicsContent['vehicleNickname'])?$this->brandDemographicsContent ['vehicleNickname']:"";
    }

    /**
     * @return string
     */
    public function getVehicleNote(){
        return  isset($this->brandDemographicsContent['shortNote'])?$this->brandDemographicsContent ['shortNote']:"";
    }

}