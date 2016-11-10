<?php
namespace Acme\DataBundle\Model\Cron;
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 11.02.2016
 * Time: 11:58
 */

use Acme\DataBundle\Entity\Dma;
use Acme\DataBundle\Entity\StoresHasDma;
use Acme\DataBundle\Model\Utility\StringUtility;
use Acme\DataBundle\Model\Utility\Notification;
use Acme\DataBundle\Model\Constants\StoresStatus;
use Acme\DataBundle\Model\Utility\Logs;

class StoresDMA extends Cron implements CronInterface
{

    public function add($csvFile, $logFile, $params=array()) {

        $finalData = $this->getCsvImportData($csvFile, $logFile);

        try {

            $startTime = date('H:i:s', time());

            $total = count($finalData);
            $newDMA = 0;
            $newStoresHasDma = 0;

            Logs::write($this->logFile , 'Start importing   ' . $total . ' DMA... at .'.$startTime.' ');

            for($i=0; $i<$total; $i++ ) {

                $csvClosed      = preg_match( "#".StoresStatus::CLOSED."#i", $finalData[$i]['statusflag']);

                $dma = $this->em->getRepository('AcmeDataBundle:Dma')->findOneBy(array("city" => $finalData[$i]['locationcity'], "state" => $finalData[$i]['locationstate']));
                $store =  $this->em->getRepository('AcmeDataBundle:Stores')->findOneByStoreId($finalData[$i]['shopnumber']);
                if(!$csvClosed) {
                    if(!$dma ) {
                        $dma = new Dma();
                        $dma->setCity($finalData[$i]['locationcity']);
                        $dma->setState($finalData[$i]['locationstate']);
                        $dma->setDmaName($finalData[$i]['dmaname']);
                        $dma->setDmaId($finalData[$i]['dmanumber']);
                        $dma->setDmaSlug(StringUtility::generateSlug($finalData[$i]['dmaname']));
                        $this->em->persist($dma);
                        $this->em->flush();
                        $newDMA++;
                    }
                    $storesHasDma = $this->em->getRepository('AcmeDataBundle:StoresHasDma')->findOneBy(array('stores'=>$store->getId(), 'dma'=>$dma->getId()));
                    if(!$storesHasDma) {
                        $storesHasDma = new StoresHasDma();
                        $storesHasDma->setDma($dma);
                        $storesHasDma->setStores($store);
                        $this->em->persist($storesHasDma);
                        $this->em->flush();
                        $newStoresHasDma++;
                    }

                } else {
                    //REMOVE MAPPED STORE IF CLOSED
                    if($dma && $store) {
                        $storesHasDma = $this->em->getRepository('AcmeDataBundle:StoresHasDma')->findOneBy(array('stores'=>$store->getId(), 'dma'=>$dma->getId()));
                        if($storesHasDma) {
                            $this->em->remove($storesHasDma);
                            $this->em->flush();
                        }
                        //REMOVE DMA IF DOESN'T HAVE STORES
                    }
                    if($dma) {
                        $dmaHasStores = $this->em->getRepository('AcmeDataBundle:StoresHasDma')->findBy(array('dma'=>$dma->getId()));
                        if(!$dmaHasStores) {
                            $this->em->remove($dma);
                            $this->em->flush();
                        }
                    }
                }
            }

            $endTime = date('H:i:s', time());

            Logs::write($this->logFile , 'End import at'. $endTime . ' ');
            Logs::write($this->logFile , $newDMA . ' newly dma');
            Logs::write($this->logFile , $newStoresHasDma . ' newly stores added to DMA');

            //delete redis cache
            $cache = $this->container->get('cacheManagementBundle.redis')->initiateCache();
            //find keys
            $keys = $cache->find('*dma*');
            //delete cache
            if(!empty($keys)) {
                for($i=0;$i<count($keys);$i++) {
                    $cache->delete($keys[$i]);
                }
                Logs::write($this->logFile , 'DMA Cache successfully deleted.');
            }

            return $this->notification = new Notification(true);

        } catch(\Exception $e) {
            Logs::write($this->logFile , $e->getMessage());
            return $this->notification = new Notification(false , $e->getMessage());
        }
    }
}