<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 01.11.2016
 * Time: 17:30
 */

namespace Acme\DataBundle\Model\Cron;

use Acme\DataBundle\Model\Utility\DataSerializer;
use Acme\DataBundle\Model\Utility\FilesUtility;
use Acme\DataBundle\Model\Utility\Logs;
use Acme\DataBundle\Model\Utility\StringUtility;
use Acme\DataBundle\Model\Utility\Notification;
use Acme\DataBundle\Entity\Mso;
use Acme\DataBundle\Entity\StoresHasMso;
use Acme\DataBundle\Model\Constants\StoresStatus;


class StoresMSO extends Cron implements CronInterface
{
    public function add($csvFile, $logFile, $params=array()) {

        $finalData = $this->getCsvImportData($csvFile, $logFile);

        try{

            $startTime = date('H:i:s', time());
            $total = count($finalData);
            $newMSO = 0;
            $newStoresHasMso = 0;

            Logs::write($this->logFile , 'Start importing   ' . $total . ' MSO... at .'.$startTime.' ');

            for($i=0; $i<$total; $i++ ) {
                $mso = $this->em->getRepository('AcmeDataBundle:Mso')->findOneBy(array("city" => $finalData[$i]['locationcity'], "state" => $finalData[$i]['locationstate']));
                $store =  $this->em->getRepository('AcmeDataBundle:Stores')->findOneByStoreId($finalData[$i]['shopnumber']);
                if($store && strtoupper($store->getLocationStatus()) !== StoresStatus::CLOSED) {
                    if(!$mso ) {
                        $mso = new Mso();
                        $mso->setCity($finalData[$i]['locationcity']);
                        $mso->setState($finalData[$i]['locationstate']);
                        $mso->setMsoName($finalData[$i]['msoname']);
                        $mso->setMsoId($finalData[$i]['msonumber']);
                        $mso->setMsoSlug(StringUtility::generateSlug($finalData[$i]['msoname']));
                        $this->em->persist($mso);
                        $this->em->flush();
                        $newMSO++;
                    }
                    $storesHasMso = $this->em->getRepository('AcmeDataBundle:StoresHasMso')->findOneBy(array('stores'=>$store->getId(), 'mso'=>$mso->getId()));
                    if(!$storesHasMso) {
                        $storesHasMso = new StoresHasMso();
                        $storesHasMso->setMso($mso);
                        $storesHasMso->setStores($store);
                        $this->em->persist($storesHasMso);
                        $this->em->flush();
                        $newStoresHasMso++;
                    }

                } else {
                    if($mso && $store) {
                        $storesHasMso = $this->em->getRepository('AcmeDataBundle:StoresHasMso')->findOneBy(array('stores'=>$store->getId(), 'mso'=>$mso->getId()));
                        if($storesHasMso) {
                            $this->em->remove($storesHasMso);
                            $this->em->flush();
                        }
                    }
                }
            }

            $endTime = date('H:i:s', time());
            Logs::write($this->logFile , 'End import at' . $endTime . ' ');
            Logs::write($this->logFile , $newMSO . ' newly mso');
            Logs::write($this->logFile , $newStoresHasMso . ' newly stores added to MSO');

            //delete redis cache
            $cache = $this->container->get('cacheManagementBundle.redis')->initiateCache();
            //find keys
            $keys = $cache->find('*mso*');
            //delete cache
            if(!empty($keys)) {
                for($i=0;$i<count($keys);$i++) {
                    $cache->delete($keys[$i]);
                }
                Logs::write($this->logFile , 'MSO Cache successfully deleted.');
            }

            return $this->notification = new Notification(true, 'Stores data successfully added/updated.');
        } catch(\Exception $e) {
            Logs::write($this->logFile , $e->getMessage());
            return $this->notification = new Notification(false , $e->getMessage());
        }
    }
}