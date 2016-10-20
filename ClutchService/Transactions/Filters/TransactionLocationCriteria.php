<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 13.10.2016
 * Time: 18:39
 */

namespace Acme\DataBundle\Model\ClutchService\Transactions\Filters;
use Acme\DataBundle\Model\ClutchService\Transactions\DataProvider\StoresProvider;
use Acme\DataBundle\Model\ClutchService\Transactions\FiltersToApply;
use Acme\DataBundle\Model\ClutchService\Transactions\Library\FilterInterface;
use Acme\DataBundle\Model\ClutchService\Transactions\Filter;

class TransactionLocationCriteria extends Filter implements FilterInterface
{
    public function execute($transaction) {

        $data = $this->handleData($transaction);

        if (FiltersToApply::TRANSACTION_LOCATION) {

            $em = $this->entityManager;
            $cache = $this->cache;

            //callback function to send in loop processing
            $data = $this->callbackTransaction($data, $em, $cache, function ($postProcessing, $em, $cache){
                        for($j=0; $j<count($postProcessing); $j++) {

                            //get location info
                            $locationInfo = StoresProvider::findByStoreId(str_replace("MK", "", $postProcessing[$j]["locationId"]), $em, $cache);

                            if(is_object($locationInfo)){
                                $postProcessing[$j]["store"] = array(
                                    'storeId' => $locationInfo->getStoreId(),
                                    'city' => $locationInfo->getLocationCity(),
                                    'state' => $locationInfo->getLocationState(),
                                    'phone' => $locationInfo->getPhone(),
                                    'semCamPhone' => $locationInfo->getSemCamPhone()
                                );
                            }
                        }
                        return $postProcessing;
                    });
        }

        $this->setUpdated($data);
        return $this->getUpdated();
    }
}