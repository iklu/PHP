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
    public $container;

    public function __construct($container = null) {
        $this->container = $container;
    }

    public function add($cronJob) {

        /** STORES */
        $importTasks["stores"] = new ImportScript('MaacoCenterInfo.csv', 'stores-cron.txt', [], new Stores($this->container));

        /** STORES SERVICES */
        $importTasks["services"] = new ImportScript('MaacoCenterInfo.csv', 'stores-cron.txt', [], new StoresServices($this->container));

        /** DMA */
        $importTasks["dma"] = new ImportScript('MaacoCenterInfo.csv', 'dma-cron.txt', [], new StoresDMA($this->container));

        /** MSO */
        $importTasks["mso"] = new ImportScript('MaacoCenterInfo.csv', 'stores-cron.txt', [], new StoresMSO($this->container));

        /** YODLE */
        $importTasks['yodle-centers'] = new ImportScript('MaacoCenterInfo.csv', 'stores-cron.txt', [], new StoresYodle($this->container));

        /** COLLISION REPAIR */
        $importTasks['center-collision-service'] = new ImportScript('center-collision-repair.csv', 'stores-center-level-cron.txt', array("type"=>'collision-repair'), new StoresCenterLevelService($this->container));

        /** AUTO PAINTING */
        $importTasks['center-auto-painting-service'] = new ImportScript('center-auto-painting.csv', 'stores-center-level-cron.txt',  array("type"=>'auto-painting'), new StoresCenterLevelService($this->container));

        /** INSURANCE CLAIM DRP */
        $importTasks['center-insurance-service'] = new ImportScript('center-insurance.csv',  'stores-center-level-cron.txt',  array("type"=>'insurance-claim-drp'), new StoresCenterLevelService($this->container));

        /** ALL LEVEL SERVICES IMPORT */
        $importTasks['all-center-level-services'][] = new ImportScript('center-collision-repair.csv', 'stores-center-level-cron.txt', array("type"=>'collision-repair'), new StoresCenterLevelService($this->container));
        $importTasks['all-center-level-services'][] = new ImportScript('center-auto-painting.csv', 'stores-center-level-cron.txt',  array("type"=>'auto-painting'), new StoresCenterLevelService($this->container));
        $importTasks['all-center-level-services'][] = new ImportScript('center-insurance.csv',  'stores-center-level-cron.txt',  array("type"=>'insurance-claim-drp'), new StoresCenterLevelService($this->container));

        /** STORES SLIDES */
        $importTasks['stores-slides'] = new ImportScript('MaacoCenterInfo.csv', 'stores-cron.txt', [], new StoresSlides($this->container));

        /** UNPAID TRACKING */
        $importTasks['unpaid-tracking'] = new ImportScript('MaacoCenterInfo.csv', 'stores-cron.txt', [], new StoresUnpaidTrackingLines($this->container));

        $response = array();
        
        if(array_key_exists($cronJob, $importTasks)) {
            if(is_array($importTasks[$cronJob])) {
                foreach($importTasks[$cronJob] as $import=>$script) {
                    $response[] = $script->run();
                }
            } else {
                $response[] = $importTasks[$cronJob]->run();
            }
        } else {
            return  new Notification(false , 'No cron job available.');
        }

        return $response;

    }
}