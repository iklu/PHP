<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 19.10.2016
 * Time: 09:44
 */

namespace Acme\DataBundle\Model\ClutchService\Transactions\Filters;
use Acme\DataBundle\Model\ClutchService\Transactions\FiltersToApply;
use Acme\DataBundle\Model\ClutchService\Transactions\Library\FilterInterface;
use Acme\DataBundle\Model\ClutchService\Transactions\Filter;


class TransactionDetailsForLastLocationCriteria extends Filter implements FilterInterface
{
    public function execute($transaction)
    {
        $data = $this->handleData($transaction);

        $transactionTime = 0;
        $transactionId = 0;
        $data["lastVisitedStoreId"] = "";

        $cardHistoryTransactions = array_values($data["cardHistoryTransactions"]);

        if (FiltersToApply::TRANSACTION_LAST_LOCATION) {
            for($i = 0; $i < count($cardHistoryTransactions); $i++ ) {
                if(intval($cardHistoryTransactions[$i]['transactionTime'] / 1000) > intval($transactionTime / 1000)) {
                    $transactionTime = $cardHistoryTransactions[$i]['transactionTime'];
                    $transactionId = $cardHistoryTransactions[$i]['transactionId'];
                }
            }

            if($transactionId) {
                $lastVisitedStoreId = $this->clutch->getTransactionDetailsForLastLocation($transactionId);
                $data["lastVisitedStoreId"] = str_replace("MK", "", $lastVisitedStoreId);

            }
        }

        $this->setUpdated($data);
        return $this->getUpdated();
    }
}