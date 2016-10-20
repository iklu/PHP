<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 19.10.2016
 * Time: 11:54
 */

namespace Acme\DataBundle\Model\ClutchService\Transactions\Filters;
use Acme\DataBundle\Model\ClutchService\Transactions\FiltersToApply;
use Acme\DataBundle\Model\ClutchService\Transactions\Library\FilterInterface;
use Acme\DataBundle\Model\ClutchService\Transactions\Filter;

class TransactionCardHistoryCriteria extends Filter implements FilterInterface
{
    public function execute($transaction)
    {
        $data = $this->handleData($transaction);

        $data["cardHistoryTransactions"] = array();

        if (FiltersToApply::TRANSACTION_CARD_HISTORY) {
            $historyTransactionsLastLocation = $this->clutch->getHistoryTransaction($data["cardNumber"], '');
            for($i=0;$i<count($historyTransactionsLastLocation);$i++) {
                if(isset($historyTransactionsLastLocation[$i]['callType']) && $historyTransactionsLastLocation[$i]['callType'] == "CHECKOUT_COMPLETE") {
                    $data["cardHistoryTransactions"][$i] = $historyTransactionsLastLocation[$i];
                }
            }
        }
        $this->setUpdated($data);
        return $this->getUpdated();
    }
}