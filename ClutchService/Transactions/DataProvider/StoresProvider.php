<?php

/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 18.10.2016
 * Time: 17:16
 */
namespace Acme\DataBundle\Model\ClutchService\Transactions\DataProvider;

class StoresProvider
{
    /**
     * Search store and save to cache
     *
     * @param $storeId
     * @param $em
     * @param $cache
     * @return mixed
     */
    public static function findByStoreId($storeId, $em, $cache){

        $cache = $cache->initiateCache();
        $cacheKey = 'transactions locations' . $storeId ;
        if ($cache->contains($cacheKey)){
            return $cache->fetch($cacheKey);
        }

        $locationInfo = $em->getRepository('AcmeDataBundle:Stores')->findOneByStoreId($storeId);
        $cache->save($cacheKey, $locationInfo);
        return $locationInfo;
    }
}