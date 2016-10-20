<?php

/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 11.10.2016
 * Time: 10:57
 */

namespace Acme\DataBundle\Model\ClutchService\Transactions\Filters;
use Acme\DataBundle\Model\ClutchService\Transactions\FiltersToApply;
use Acme\DataBundle\Model\ClutchService\Transactions\Library\FilterInterface;
use Acme\DataBundle\Model\ClutchService\Transactions\Filter;

class TransactionSkuTypeCriteria extends Filter implements FilterInterface
{
    function execute($transaction ) {

        $data = $this->handleData($transaction);

        if (FiltersToApply::SKU_TYPE_1) {

            $data = $this->callbackTransaction($data, "","", function ($postProcessing) {
                        $transactionFiltered = array();
                        for ($j = 0; $j < count($postProcessing); $j++) {
                            preg_match_all('~\b(memo|disc|discount)\b~i', strtolower($postProcessing[$j]["sku"]), $matches);
                            if (!$matches[0]) {
                                $transactionFiltered[$j] = $postProcessing[$j];
                            }
                        }
                        $transactionFiltered = array_values($transactionFiltered);
                        return $transactionFiltered;
                    });
        }

        if (FiltersToApply::SKU_TYPE_2) {

            $data = $this->callbackTransaction($data, "","", function ($postProcessing) {
                        $transactionFiltered = array();
                        for ($j = 0; $j < count($postProcessing); $j++) {
                            if ($postProcessing[$j]["sku"] !== "0-0, MEMO" && $postProcessing[$j]["sku"] !== "0-0, Discount") {
                                $transactionFiltered[$j] = $postProcessing[$j];
                            }
                        }
                        $transactionFiltered = array_values($transactionFiltered);
                        return $transactionFiltered;
                    });
        }

        $this->setUpdated($data);
        return $this->getUpdated();
    }
}

