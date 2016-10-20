<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 17.10.2016
 * Time: 18:47
 */

namespace Acme\DataBundle\Model\ClutchService\Transactions;


class Target
{
    public function execute($transaction) {

        $data["cardNumber"] = $transaction["transactionsData"]["cardNumber"];
        $data["customCardNumber"] = $transaction["transactionsData"]["customCardNumber"];
        $data["firstName"] = $transaction["transactionsData"]["firstName"];
        $data["lastName"] = $transaction["transactionsData"]["lastName"];
        $data["email"] = $transaction["transactionsData"]["email"];
        $data["phone"] = $transaction["transactionsData"]["phone"];
        $data["balance"] = $transaction["transactionsData"]["balance"];
        $data["lastVisitedStoreId"] = $transaction["transactionsData"]["lastVisitedStoreId"];
        $data["serviceReminders"] = $transaction["transactionsData"]["serviceReminders"];
        $data["cardHistoryTransactions"] = $transaction["transactionsData"]["cardHistoryTransactions"];
        $data["vehicles"] = $transaction["transactionsData"]["vehicles"];
        
        return $data;
    }
}