<?php
namespace Acme\DataBundle\Model\Cron;
use Acme\DataBundle\Model\Utility\StringUtility;
use Acme\DataBundle\Model\Utility\Notification;
use Acme\DataBundle\Model\Constants\StoresStatus;
use Acme\DataBundle\Model\Utility\EntitiesUtility;
use Acme\DataBundle\Entity\StoresHasServices;
use Acme\DataBundle\Model\Utility\DataSerializer;
use Acme\DataBundle\Model\Utility\Logs;
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 27.01.2016
 * Time: 15:04
 */
class StoresServices extends Cron implements CronInterface
{
    public function add($csvFile, $logFile,  $params=array()) {

        $finalData = $this->getCsvImportData($csvFile, $logFile);
        
        try {
            $total = count($finalData);
            for($i=0;$i<$total;$i++) {
                //stores are updated if they are not closed
                if(strtoupper($finalData[$i]['statusflag']) !== StoresStatus::CLOSED) {
                    $checkStore = $this->em->getRepository('AcmeDataBundle:Stores')->findOneByStoreId($finalData[$i]['shopnumber']);
                    if($checkStore)
                        $entity = $checkStore;

                    //add store services
                    $services = EntitiesUtility::getCSVServices();
                    for($j=0;$j<count($services);$j++) {
                        $checkService = $this->em->getRepository('AcmeDataBundle:Services')->findOneByTitle($services[$j]);

                        if($checkService) {

                            $checkStoreService = $this->em->getRepository('AcmeDataBundle:StoresHasServices')->findOneBy(array('stores' => $entity, 'services' => $checkService));

                            if($finalData[$i][$services[$j]]) {
                                if(!$checkStoreService) {
                                    //add to DB
                                    $entitySHS = new StoresHasServices();
                                    $entitySHS->setStores($entity);
                                    $entitySHS->setServices($checkService);

                                    $this->em->persist($entitySHS);
                                    $this->em->flush();
                                }
                            }
                            else {
                                if($checkStoreService) {
                                    //remove from DB
                                    $this->em->remove($checkStoreService);
                                    $this->em->flush();
                                }
                            }
                        }
                    }

                    //Add stores has center level service description after the service has been added fro the new store
                    //No need for verification if the store has the respective service , is done in the StoresCenterLevelService class
                    $storesHasCenterLevelServices = new StoresCenterLevelService($this->container);
                    $checkIfStoreCenterLevelService = $this->em->getRepository('AcmeDataBundle:StoresHasCenterLevelService')->findOneBy(array('stores' => $checkStore->getId()));

                    $store = DataSerializer::deserializeEntityToArray($checkStore);

                    if(!$checkIfStoreCenterLevelService) {
                        $storesHasCenterLevelServices->generateParagraphsMappingSerialization($store, 'collision-repair');
                        $storesHasCenterLevelServices->generateParagraphsMappingSerialization($store, 'auto-painting');
                        $storesHasCenterLevelServices->generateParagraphsMappingSerialization($store, 'insurance-claim-drp');
                    }
                }
            }

            //delete redis cache
            $cache = $this->container->get('cacheManagementBundle.redis')->initiateCache();

            //find keys
            $keys = $cache->find('*service*');

            //delete cache
            if(!empty($keys)) {
                for ($i = 0; $i < count($keys); $i++) {
                    $cache->delete($keys[$i]);
                }
                Logs::write($this->logFile , 'Stores Services Cache successfully deleted.');
            }

            return $this->notification = new Notification(true);

        } catch (\Exception $e) {
            Logs::write($this->logFile , $e->getMessage());
            return $this->notification = new Notification(false , $e->getMessage());
        }
    }
}