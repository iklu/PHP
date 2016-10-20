<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 11.10.2016
 * Time: 12:56
 */

namespace Acme\DataBundle\Model\ClutchService\Transactions;




use Acme\DataBundle\Model\ClutchService\Vehicles\VehiclesManager;

class TransactionManager
{

    public $vehicles;

    public $clutch;

    public $customer;

    public $entityManager;

    public $cache;


    /**
     * Transaction constructor.
     * @param VehiclesManager $vehicles
     */
    public function __construct(VehiclesManager $vehicles) {
        $this->vehicles = $vehicles->getAllVehicles();
        $this->clutch = $vehicles->clutchAccount->getClutchService();
        $this->customer = $vehicles->clutchAccount->getCustomerCardNumber();
        $this->entityManager = $vehicles->clutchAccount->getEntityManager();
        $this->cache = $vehicles->clutchAccount->getCache();
    }


    public function formatTransaction(){

        /** @var  $customer */
        $customer = array();
        $customer["cardNumber"] = $this->customer->getCardNumber();
        $customer["customCardNumber"] = $this->customer->getCustomCardNumber();
        $customer["firstName"] = $this->customer->getFirstName();
        $customer["lastName"] = $this->customer->getLastName();
        $customer["email"] = $this->customer->getEmail();
        $customer["phone"] = $this->customer->getPhone();
        $customer["balance"] = $this->customer->getBalance();

        /** @var  $vehicle */
        $vehicle = array();
        for($i = 0; $i<count($this->vehicles); $i++) {

            $vehicle["vehicles"][$i]["vehicleId"] =  $this->vehicles[$i]["vehicleId"];
            $vehicle["vehicles"][$i]["vehicle"] = $this->vehicles[$i]["make"] . ' ' . $this->vehicles[$i]['model'];
            $vehicle["vehicles"][$i]["make"] =  $this->vehicles[$i]["make"];
            $vehicle["vehicles"][$i]["year"] =  $this->vehicles[$i]["year"];
            $vehicle["vehicles"][$i]["model"] =  $this->vehicles[$i]["model"];
            $vehicle["vehicles"][$i]["vin"] =  $this->vehicles[$i]["vin"];
            $vehicle["vehicles"][$i]["tag"] =  $this->vehicles[$i]["tag"];
            $vehicle["vehicles"][$i]["image"] =  $this->vehicles[$i]["image"];
            $vehicle["vehicles"][$i]["vehicleNickname"] =  $this->vehicles[$i]["vehicleNickname"];
            $vehicle["vehicles"][$i]["shortNote"] =  $this->vehicles[$i]["shortNote"];
            $vehicle["vehicles"][$i]["mailings"] =  $this->vehicles[$i]["mailings"];
            $vehicle["vehicles"][$i]["historyTransactions"] =  $this->vehicles[$i]["historyTransactions"];

        }

        $formattedData = array(
            "clutch"=>$this->clutch,
            "cache"=>$this->cache,
            "entityManager"=>$this->entityManager,
            "transactionsData"=>array_merge($customer, $vehicle)
        );

        return $formattedData;
    }

    public static function getSkuRemindersType1(){
        return array('110-000', '110-228', '110-228P', '110-228S', '110-229', '110-229S', '110-229P', '110-305', '110-305P', '110-305S', '110-331', '110-331S', '110-331P', '110-332', '110-332S', '110-332P', '110-333', '110-333S', '110-333P');
    }

    public static function getSkuRemindersType2(){
        return array('114-000', '114-247', '114-248');
    }

}