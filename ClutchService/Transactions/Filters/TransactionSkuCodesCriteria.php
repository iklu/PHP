<?php

/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 11.10.2016
 * Time: 10:57
 */

namespace Acme\DataBundle\Model\ClutchService\Transactions\Filters;
use Acme\DataBundle\Model\ClutchService\Transactions\DataProvider\SkuCodesProvider;
use Acme\DataBundle\Model\ClutchService\Transactions\FiltersToApply;
use Acme\DataBundle\Model\ClutchService\Transactions\Library\FilterInterface;
use Acme\DataBundle\Model\ClutchService\Transactions\Filter;

class TransactionSkuCodesCriteria extends Filter implements FilterInterface
{
    function execute($transaction ) {

        $data = $this->handleData($transaction);

        if (FiltersToApply::SKU_TYPE_1) {

            $em = $this->entityManager;
            $cache = $this->cache;

            $data = $this->callbackTransaction($data, $em, $cache, function ($postProcessing, $em, $cache){

                        for ($j = 0; $j < count($postProcessing); $j++) {
                            $postProcessing[$j]["service"] = $postProcessing[$j]["sku"];

                            if (strpos($postProcessing[$j]["sku"], ', ') !== false) {
                                $postProcessing[$j]["service"] = $postProcessing[$j]["sku"];

                                $skuArr = explode(",", $postProcessing[$j]["sku"]);
                                $skuSecond = explode("-", $skuArr[0]);

                                $service = SkuCodesProvider::findOneBySkuCode(trim($skuSecond[1]) . "-" . trim($skuSecond[0]), $em, $cache);

                                if ($service != NULL) {
                                    $postProcessing[$j]["service"] = $service->getDisplayName();
                                } else {
                                    $result = '';
                                    if (!empty($skuSecond[0]) && !empty($skuSecond[1])) {
                                        $result = SkuCodesProvider::findBySkuOld($skuSecond[0], $skuSecond[1], $em, $cache);
                                    }
                                    $postProcessing[$j]["service"] = $result;
                                    $postProcessing[$j]["skuName"] = $postProcessing[$j]["sku"];
                                }

                            } else {

                                $service = SkuCodesProvider::findOneBySkuCode($postProcessing[$j]["sku"], $em, $cache);

                                if ($service != NULL) {
                                    $postProcessing[$j]["service"] = $service->getDisplayName();
                                } else {
                                    $postProcessing[$j]["service"] = NULL;
                                }
                            }
                        }
                        return $postProcessing;

                    });
        }

        $this->setUpdated($data);
        return $this->getUpdated();
    }
}

