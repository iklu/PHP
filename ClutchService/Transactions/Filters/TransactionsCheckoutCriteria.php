<?php

/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 11.10.2016
 * Time: 10:54
 */
namespace Acme\DataBundle\Model\ClutchService\Transactions\Filters;
use Acme\DataBundle\Model\ClutchService\Transactions\FiltersToApply;
use Acme\DataBundle\Model\ClutchService\Transactions\Filter;
use Acme\DataBundle\Model\ClutchService\Transactions\Library\FilterInterface;


class TransactionsCheckoutCriteria extends Filter implements FilterInterface
{
    public function execute($transaction ) {

        $data = $this->handleData($transaction);

        if (FiltersToApply::CHECKOUT_COMPLETE) {
            $data = $this->callbackHistoryTransaction($data, "", "", $this->clutch, function ($postProcessing, $em, $cache, $clutch){
                $filteredData = array();
                if($postProcessing["callType"] ==  FiltersToApply::CHECKOUT_COMPLETE) {
                    $filteredData = $postProcessing;
                    $filteredData["transactionDetails"] = $clutch->getTransactionDetails($postProcessing["transactionId"]);
                }
                return $filteredData;
            });
        }

        $this->setUpdated($data);
        return $this->getUpdated();
    }
}