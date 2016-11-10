<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 10.11.2016
 * Time: 15:39
 */

namespace Acme\DataBundle\Model\Cron;
use Acme\DataBundle\Model\Constants\StoresStatus;
use Acme\DataBundle\Model\Utility\Logs;
use Acme\DataBundle\Model\Utility\Notification;


class StoresUnpaidTrackingLines extends Cron implements CronInterface
{
    public function add($csvFile, $logFile, $params = array()) {
        $finalData = $this->getCsvImportData($csvFile, $logFile);
        try {

            $startTime = date('H:i:s', time());
            $total = count($finalData);
            $newPhone = 0;

            Logs::write($this->logFile, 'Start importing   ' . $total . 'unpaid tracking phones... at .'.$startTime.' ');
            for($i=0;$i<count($finalData);$i++) {

                preg_match('#\(([^\)]+)\)#', $finalData[$i]['yodle_name'], $matches);
                $storeId = str_replace('#', '', $matches[1]);

                //check if we have store id in database
                $checkStore = $this->em->getRepository('AcmeDataBundle:Stores')->findOneByStoreId($storeId);

                if($checkStore && $checkStore->getLocationStatus() != StoresStatus::CLOSED ) {
                    if($checkStore->getRawTrackingPhone() != $finalData[$i]['center_unpaidtrack']){
                        $checkStore->setRawTrackingPhone($finalData[$i]['center_unpaidtrack']);
                        $this->em->persist($checkStore);
                        $this->em->flush();
                        Logs::write($this->logFile, '#'.$storeId.'New Phone . '  .$finalData[$i]['center_unpaidtrack']);
                        $newPhone++;
                    }
                }
            }
            $endTime = date('H:i:s', time());
            Logs::write($this->logFile, 'End import at'. $endTime . ' ');
            Logs::write($this->logFile, $newPhone . ' newly unpaid call tracking numbers ');

            //delete redis cache
            $cache = $this->container->get('cacheManagementBundle.redis')->initiateCache();
            //find keys
            $keys = $cache->find('*stores*');
            //delete cache
            if(!empty($keys)) {
                for($i=0;$i<count($keys);$i++) {
                    $cache->delete($keys[$i]);
                }
            }

            return $this->notification = new Notification(true);

        } catch(\Exception $e) {
            Logs::write($this->logFile, $e->getMessage());
            return $this->notification = new Notification(false , $e->getMessage());
        }
    }
}