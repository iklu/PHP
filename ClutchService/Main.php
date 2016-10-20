<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 11.10.2016
 * Time: 11:02
 */

namespace Acme\DataBundle\Model\ClutchService;

use Acme\DataBundle\Model\ClutchService\Account\ClutchAccount;
use Acme\DataBundle\Model\ClutchService\Transactions\Filters\TransactionCardHistoryCriteria;
use Acme\DataBundle\Model\ClutchService\Transactions\Filters\TransactionDetailsForLastLocationCriteria;
use Acme\DataBundle\Model\ClutchService\Transactions\Library\FilterManager;
use Acme\DataBundle\Model\ClutchService\Transactions\Filters\TransactionLocationCriteria;
use Acme\DataBundle\Model\ClutchService\Transactions\Filters\TransactionServiceRemindersCriteria;
use Acme\DataBundle\Model\ClutchService\Transactions\Filters\TransactionSkuTypeCriteria;
use Acme\DataBundle\Model\ClutchService\Transactions\Filters\TransactionSkuCodesCriteria;
use Acme\DataBundle\Model\ClutchService\Transactions\Transaction;
use Acme\DataBundle\Model\ClutchService\Transactions\Filters\TransactionsCheckoutCriteria;
use Acme\DataBundle\Model\ClutchService\Transactions\Target;
use Acme\DataBundle\Model\ClutchService\Vehicles\VehiclesManager;



class Main
{
    /**
     * @var
     */
    public $email;

    /**
     * @var
     */
    public $phone;

    /**
     * @var
     */
    public $cardNumber;

    /**
     * @var
     */
    public $clutchCardNumberResults;

    /**
     * @var
     */
    public $vehicles;

    /**
     * @var
     */
    public $transactions;

    /**
     * @var
     */
    public $container;

    /**
     * Main constructor.
     * @param $container
     * @param string $email
     * @param string $phone
     * @param string $cardNumber
     */
    public function __construct($container, $email = "", $phone = "", $cardNumber = ""){
        $this->email = $email;
        $this->phone = $phone;
        $this->cardNumber =$cardNumber;
        $this->container = $container;
    }

    /** Get the customer data from Clutch */
    public function getCustomer() {
        $this->clutchCardNumberResults = new ClutchAccount($this->container);
        $this->clutchCardNumberResults->searchByCustomerData($this->email, $this->phone);
    }

    /** Get the vehicles from the customer */
    public function getVehicles(){
        $this->vehicles = new VehiclesManager($this->clutchCardNumberResults, $this->container);
        //$this->vehicles->getAllVehicles();
        return $this->vehicles;
    }

    /** Get the transactions for the vehicles */
    public function getTransactions() {

        $filterManager = new FilterManager(new Target());

        $filterManager->setFilter(new TransactionCardHistoryCriteria());
        $filterManager->setFilter(new TransactionsCheckoutCriteria());
        $filterManager->setFilter(new TransactionLocationCriteria());
        $filterManager->setFilter(new TransactionSkuTypeCriteria());
        $filterManager->setFilter(new TransactionServiceRemindersCriteria());
        $filterManager->setFilter(new TransactionSkuCodesCriteria());
        $filterManager->setFilter(new TransactionDetailsForLastLocationCriteria());

        $this->transactions = new Transaction($this->getVehicles());
        $this->transactions->setFilterManager($filterManager);
        return $this->transactions->handleTransaction();
    }
}