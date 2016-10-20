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

            $transactionFiltered = array();

            for($v = 0; $v < count($data["vehicles"]); $v++){
                for($i = 0; $i<count($data["vehicles"][$v]["historyTransactions"]); $i++){

                    if($data["vehicles"][$v]["historyTransactions"][$i]["callType"] ==  FiltersToApply::CHECKOUT_COMPLETE) {
                        $transactionFiltered[$i] = $data["vehicles"][$v]["historyTransactions"][$i];
                        $transactionFiltered[$i]["transactionTime"] = date("m/d/y", intval( $data["vehicles"][$v]["historyTransactions"][$i]['transactionTime'] / 1000));
                        $transactionFiltered[$i]["transactionDetails"] = $this->clutch->getTransactionDetails($data["vehicles"][$v]["historyTransactions"][$i]["transactionId"]);
                    }
                }
                $data["vehicles"][$v]["historyTransactions"] = array_values($transactionFiltered);
            }
        }

        $this->setUpdated($data);
        return $this->getUpdated();
    }
}