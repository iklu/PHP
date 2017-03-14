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
use Acme\DataBundle\Model\Utility\DataSerializer;
use Acme\DataBundle\Model\Utility\Logs;
use Acme\DataBundle\Model\Utility\Notification;


class StoresSlides extends Cron implements CronInterface
{
    public function add($csvFile, $logFile, $params = array())
    {

        $finalData = $this->getCsvImportData($csvFile, $logFile);

        try {

            $data = [];
            $increment = 0;

            /** PARSE BANNERS */
            for ($i = 1; $i < count($finalData); $i++) {

                $banners = [];

                //Parse banners and videoLinks
                foreach ($finalData[$i] as $key => $value) {
                    if (preg_match("#banner#", $key)) {
                        $banners[$key] = $finalData[$i][$key];
                    }
                    if (preg_match("#click#", $key)) {
                        $banners[$key] = $finalData[$i][$key];
                    }
                }

                foreach ($banners as $key => $value) {

                    if (preg_match("#banner#", $key)) {
                        if ($value != "") {
                            ++$increment;
                            $data[$increment]["storeId"] = $finalData[$i]["center number"];
                            $data[$increment]["banner"] = $value;
                        }
                    }

                    if (preg_match("#click#", $key)) {
                        if ($value != "") {
                            $data[$increment]["clickURL"] = $value;
                        }
                    }
                }
                unset($banners);
            }

            $data = array_values($data);

            $toRemove = [];

            /** ADD BANNERS AND MAPPING */
            for($i=0; $i<count($data); $i++ ) {

                if ($data[$i]['banner'] != ""){
                    $banner = $this->em->getRepository('AcmeDataBundle:StoresBanners')->findOneBy(array("bannerImage" => $data[$i]['banner'].".jpg"));
                    if (!$banner) {
                        $banner = new StoresBanners();
                        $banner->setBannerImage($data[$i]['banner'].".jpg");
                    }

                    $bannerURL = $data[$i]['clickURL'] == "N/A"?"" : $data[$i]['clickURL'];

                    $banner->setBannerUrl($bannerURL);
                    $banner->setBannerType("custom");
                    $this->em->persist($banner);
                    $this->em->flush();

                    $store =  $this->em->getRepository('AcmeDataBundle:Stores')->findOneByStoreId($data[$i]['storeId']);
                    if($store && $store->getLocationStatus() != StoresStatus::CLOSED) {
                        $storesHasBanners = $this->em->getRepository('AcmeDataBundle:StoresHasBanners')->findBy(array('stores'=>$store->getId(), 'storesBanners'=>$banner->getId()));
                        foreach ($storesHasBanners as $value){
                            $toRemove[] = $value->getId();
                        }

                        $storesHasBanners = new StoresHasBanners();
                        $storesHasBanners->setStoresBanners($banner);
                        $storesHasBanners->setStores($store);
                        $storesHasBanners->setIsActive(true);
                        $this->em->persist($storesHasBanners);
                        $this->em->flush();
                    }
                }
            }

            /**REMOVE OLD MAPPING */
            foreach($toRemove as $key=>$value) {
                if($value) {
                    $removeMap = $this->em->getRepository('AcmeDataBundle:StoresHasBanners')->findOneById($value);
                    if($removeMap) {
                        $this->em->remove($removeMap);
                        $this->em->flush();
                    }
                }
            }

            return $this->notification = new Notification(true);

        } catch (\Exception $e) {
            Logs::write($this->logFile, $e->getMessage());
            return $this->notification = new Notification(false, $e->getMessage());
        }
    }
}