<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 04.11.2016
 * Time: 14:28
 */

namespace Acme\DataBundle\Model\Cron;

use Acme\DataBundle\Entity\StoresBanners;
use Acme\DataBundle\Entity\StoresHasBanners;
use Acme\DataBundle\Model\Constants\StoresStatus;
use Acme\DataBundle\Model\Utility\Logs;
use Acme\DataBundle\Model\Utility\Notification;


class StoresSlides extends Cron implements CronInterface
{
    public function add($csvFile, $logFile, $params=array()) {

        $finalData = $this->getCsvImportData($csvFile, $logFile);
        try{

            for($i=0; $i<count($finalData); $i++ ) {

                if( $finalData[$i]['banner'] != ""){
                    $banner = $this->em->getRepository('AcmeDataBundle:StoresBanners')->findOneBy(array("bannerImage" => $finalData[$i]['banner'].".jpg"));
                    $store =  $this->em->getRepository('AcmeDataBundle:Stores')->findOneByStoreId($finalData[$i]['center']);

                    if(!$banner ) {
                        $banner = new StoresBanners();
                        $banner->setBannerImage($finalData[$i]['banner'].".jpg");
                        $banner->setBannerUrl($finalData[$i]['click-link']);
                        $banner->setBannerType("custom");
                        $this->em->persist($banner);
                        $this->em->flush();
                    }

                    if($store && $store->getLocationStatus() != StoresStatus::CLOSED) {
                        $storesHasDma = $this->em->getRepository('AcmeDataBundle:StoresHasBanners')->findOneBy(array('stores'=>$store->getId(), 'storesBanners'=>$banner->getId()));
                        if(!$storesHasDma) {
                            $storesHasDma = new StoresHasBanners();
                            $storesHasDma->setStoresBanners($banner);
                            $storesHasDma->setStores($store);
                            $storesHasDma->setIsActive(true);
                            $this->em->persist($storesHasDma);
                            $this->em->flush();
                        }
                    }
                }
            }

            return $this->notification = new Notification(true);

        }catch(\Exception $e){
            Logs::write($this->logFile , $e->getMessage());
            return $this->notification = new Notification(false , $e->getMessage());
        }
    }
}