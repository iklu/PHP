<?php
namespace Acme\DataBundle\Model\Cron;
use Acme\DataBundle\Model\Utility\Notification;

/**
 * Created by PhpStorm.
 * User: ovidiu
 * Factory Pattern Applied
 * Date: 27.01.2016
 * Time: 15:10
 */
class CronManager
{
    public function __construct($container = null) {
        $this->container = $container;
    }

    public function add($cronJob) {
        switch($cronJob) {
            case 'stores':
                $store = new Stores($this->container);
                return $store->add('Maaco_LocationsCSV_20160525_v1.csv');
                break;
            case 'accounts':
                $store = new StoresAccounts($this->container);
                return $store->add('MeinekeCenterInfo.csv');
                break;
            case 'services':
                $store = new StoresServices($this->container);
                return $store->add('MeinekeCenterInfo.csv');
                break;
            case 'dma':
                $store = new StoresDMA($this->container);
                return $store->add('MeinekeCenterInfo.csv');
                break;
            case 'organic-tracking-numbers':
                $store = new StoresTrackingPhone($this->container);
                return $store->add('organic_dni_ct_phone.csv');
                break;
            case 'services-prices':
                $store = new ServicesPrices($this->container);
                return $store->add('services_prices.csv');
                break;
            case 'yodle-centers':
                $store = new StoresYodle($this->container);
                return $store->add('yodle_center.csv');
                break;
            case 'center-collision-service':
                $store = new StoresCenterLevelService($this->container);
                return $store->add('center-collision-repair.csv', 'collision-repair');
                break;
            case 'center-auto-painting-service':
                $store = new StoresCenterLevelService($this->container);
                return $store->add('center-auto-painting.csv', 'auto-painting');
                break;
            case 'center-insurance-service':
                $store = new StoresCenterLevelService($this->container);
                return $store->add('center-insurance.csv', 'insurance-claim-drp');
                break;
            
            default:
                return  new Notification(false , 'No cron job available.');
        }
    }
}