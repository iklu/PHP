<?php

/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 18.10.2016
 * Time: 17:15
 */
namespace Acme\DataBundle\Model\ClutchService\Transactions\DataProvider;

class SkuCodesProvider
{

    public static function findBySkuOld($skuId1, $skuId2, $em, $cache){
        
        $cache = $cache->initiateCache();
        $cacheKey = 'sku old classes ' . $skuId1.$skuId2 ;
        if ($cache->contains($cacheKey)){
            return $cache->fetch($cacheKey);
        }

        $result = '';

        $stmt = $em->getDoctrine()->getManager()
            ->getConnection()
            ->prepare('select * from sku_old_lines where id = :id');
        @$stmt->bindParam(':id', $skuId1);
        $stmt->execute();
        $result1 = $stmt->fetchAll();

        $stmt2 = $em->getManager()
            ->getConnection()
            ->prepare('select * from sku_old_classes where id = :id');
        @$stmt2->bindParam(':id', $skuId2);
        $stmt2->execute();
        $result2 = $stmt->fetchAll();
        @$result = $result1[0]["longdescription"] . $result2[0]["longdescription"];

        $cache->save($cacheKey, $result);
        return $result;
    }

    public static function findOneBySkuCode($skuCode, $em, $cache){

        $cache = $cache->initiateCache();
        $cacheKey = 'find one by sku code ' . $skuCode ;
        if ($cache->contains($cacheKey)){
            return $cache->fetch($cacheKey);
        }

        $service = $em->getRepository('AcmeDataBundle:Sku')->findOneBySkuCode($skuCode);

        $cache->save($cacheKey, $service);
        return $service;
    }
}