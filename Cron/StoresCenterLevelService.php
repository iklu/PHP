<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 21.06.2016
 * Time: 15:23
 */

namespace Acme\DataBundle\Model\Cron;

use Acme\DataBundle\Entity\CenterLevelService;
use Acme\DataBundle\Entity\StoresHasCenterLevelService;
use Acme\DataBundle\Model\Utility\DataSerializer;
use Acme\DataBundle\Model\Utility\Logs;
use Acme\DataBundle\Model\Utility\StringUtility;
use Acme\DataBundle\Model\Utility\Notification;
use Acme\DataBundle\Model\Constants\StoresStatus;
use Symfony\Component\Security\Acl\Exception\Exception;


class StoresCenterLevelService extends Cron implements CronInterface
{
    /**
     * @param string $csvFile
     * @param string $logFile
     * @param array $params
     * @return Notification
     */
    public function add($csvFile, $logFile="", $params=array()) {

        $finalData = $this->getCsvImportData($csvFile, $logFile);
        $type = $params["type"];
        $paragraphs = array();
        //adjust this values to proper deepness for paragraphs and videos
        $deepLevelVersions = 4;
        $deepVideoVersions = 3;

        try {

            //parse data
            for($i=0; $i<count($finalData); $i++ ) {

                for($j=1; $j<$deepLevelVersions; $j++){

                    //header
                    if(array_key_exists("header", $finalData[$i])){
                        $paragraphs[$i][$j]["header"] = $finalData[$i]["header"];
                    }

                    //paragraph
                    if(array_key_exists("version ".$j, $finalData[$i])){
                        $paragraphs[$i][$j]["paragraph"] = $finalData[$i]["version ".$j];
                    }

                    //videos
                    $paragraphs[$i][$j]["videos"] = array();
                    for($k=1; $k < $deepVideoVersions; $k++){

                        $title =  "video ".$k." title";
                        $link =  "video ".$k." link";
                        $thumb = "video ".$k." thumb";

                        if(array_key_exists($title, $finalData[$i]) && !empty($finalData[$i][$title]))
                            $paragraphs[$i][$j]["videos"][$k]["title"] = $finalData[$i][$title];

                        if(array_key_exists($link, $finalData[$i]) && !empty($finalData[$i][$link]))
                            $paragraphs[$i][$j]["videos"][$k]["link"] = $finalData[$i][$link];

                        if(array_key_exists($thumb, $finalData[$i]) && !empty($finalData[$i][$thumb]))
                            $paragraphs[$i][$j]["videos"][$k]["thumb"] = $finalData[$i][$thumb];
                    }
                }
            }

            $this->generateParagraphsProviderSerialization($paragraphs, $type);
            $stores = $this->em->getRepository('AcmeDataBundle:Stores')->findBy(array("locationStatus" => StoresStatus::OPEN));
            $this->generateParagraphsMappingSerialization(DataSerializer::deserializeEntityToArray($stores), $type);

            //delete redis cache
            $cache = $this->container->get('cacheManagementBundle.redis')->initiateCache();

            //find keys
            $keys = $cache->find('*center-level-service*');
            //delete cache
            if(!empty($keys)) {
                for($i=0;$i<count($keys);$i++) {
                    $cache->delete($keys[$i]);
                }
            }

            return $this->notification = new Notification(true, 'Stores data successfully added/updated.');

        } catch(\Exception $e) {
            Logs::write($this->logFile , $e->getMessage());
            return $this->notification = new Notification(false , $e->getMessage());
        }
    }

    /**
     * @param array $stores
     * @param $type
     * @return Notification
     */
    public function generateParagraphsMappingSerialization(array $stores, $type) {

        try {

            if(!key_exists(0, $stores)) {
                $arrayStores[0] = $stores;
            } else {
                $arrayStores = $stores;
            }

            foreach($arrayStores as $key=>$st) {

                $id = $st["id"];
                $storeId = $st["store_id"];

                //Search if FZ has oil change service
                $services = $this->em->getRepository('AcmeDataBundle:StoresHasServices')->getStateServices('', $storeId);

                $contains = false;
                foreach($services as $value) {
                    if($value['slug'] == $type || $value['parentLvl1_slug'] == $type || $value['parentLvl2_slug'] == $type) {
                        $contains = true;
                        break;
                    }
                }

                if($contains === true) {
                    $storesHasCenterLevelService = $this->em->getRepository('AcmeDataBundle:StoresHasCenterLevelService')->findOneByStores($id);

                    //set paragraphs
                    if(!$storesHasCenterLevelService) {
                        $storesHasCenterLevelService = new StoresHasCenterLevelService();
                    }

                    $findAllParagraphs =  $this->em->getRepository('AcmeDataBundle:CenterLevelService')->findBy(array("type"=>$type));
                    $paragraphArray = array();

                    if($findAllParagraphs) {
                        for($i=0; $i<count($findAllParagraphs); $i++) {
                            $data[$i]['paragraph'] =  $findAllParagraphs[$i]->getParagraph();
                            $data[$i]['templates'] = array_rand($findAllParagraphs[$i]->getTemplates());
                            $paragraphArray[$i] = array('paragraphNumber'=>$data[$i]['paragraph'], 'templateIndex'=>$data[$i]['templates']);
                        }

                        $storeParagraphs = $storesHasCenterLevelService->getParagraphs();
                        $storeParagraphs[$type] = $paragraphArray;

                        $store =  $this->em->getRepository('AcmeDataBundle:Stores')->find($id);
                        $storesHasCenterLevelService->setStores($store);
                        $storesHasCenterLevelService->setParagraphs($storeParagraphs);
                        $this->em->persist($storesHasCenterLevelService);
                        $this->em->flush();
                    }
                }
            }
        } catch (Exception $e) {
            Logs::write($this->logFile , $e->getMessage());
            return $this->notification = new Notification(false , $e->getMessage());
        }
    }

    /**
     * @param array $paragraphs
     * @param $type
     */
    public function generateParagraphsProviderSerialization(array $paragraphs, $type){
        //check if paragraphs exists in db
        foreach($paragraphs as $paragraph => $templates) {
            $centerLevelService = $this->em->getRepository('AcmeDataBundle:CenterLevelService')->findOneBy(array("paragraph" => $paragraph , "type"=>$type));
            if(!$centerLevelService) {
                $centerLevelService = new CenterLevelService();
                $centerLevelService->setParagraph($paragraph);
                $centerLevelService->setTemplates($templates);
                $centerLevelService->setType($type);
                $this->em->persist($centerLevelService);
                $this->em->flush();
            }
        }
    }
}