<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 17.10.2016
 * Time: 18:47
 */

namespace Acme\DataBundle\Model\ClutchService\Transactions;


use Acme\DataBundle\Model\Utility\StringUtility;

class Target
{
    public function execute($transaction) {

        //important array_values needed in some filters for indexing in order

        $data = array();

        //get the transaction details that we need
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


        $toReturn = array();
        $toReturn["historyTransactions"] = array();

        //VEHICLES
        for($i = 0; $i< count($data["vehicles"]); $i++) {

            /** @var  $vehicle */
            $vehicle =  $data["vehicles"][$i]["vehicle"];

            $toReturn["vehicles"][$i]['make'] =             $data["vehicles"][$i]['make'];
            $toReturn["vehicles"][$i]['year'] =             $data["vehicles"][$i]['year'];
            $toReturn["vehicles"][$i]['model'] =            $data["vehicles"][$i]['model'];
            $toReturn["vehicles"][$i]['vin'] =              $data["vehicles"][$i]['vin'];
            $toReturn["vehicles"][$i]['tag'] =              $data["vehicles"][$i]['tag'];
            $toReturn["vehicles"][$i]['image'] =            $data["vehicles"][$i]['image'];
            $toReturn["vehicles"][$i]['vehicleNickname'] =  $data["vehicles"][$i]['vehicleNickname'];
            $toReturn["vehicles"][$i]['shortNote'] =        $data["vehicles"][$i]['shortNote'];

            $index = 0;

            //TRANSACTIONS
            for($j = 0; $j < count($data["vehicles"][$i]["historyTransactions"]); $j++ ) {

                $transactionTime =  date("m/d/y", intval($data["vehicles"][$i]["historyTransactions"][$j]["transactionTime"] / 1000));

                //TRANSACTION DETAILS
                for($k = 0; $k < count($data["vehicles"][$i]["historyTransactions"][$j]["transactionDetails"]); $k++ ) {
                    $index++;
                    $toReturn["historyTransactions"][$vehicle][$index]["transactionTime"]  = $transactionTime;
                    $toReturn["historyTransactions"][$vehicle][$index]["amount"]           = $data["vehicles"][$i]["historyTransactions"][$j]["transactionDetails"][$k]["amount"];
                    $toReturn["historyTransactions"][$vehicle][$index]["store"]            = $data["vehicles"][$i]["historyTransactions"][$j]["transactionDetails"][$k]["store"];
                    $toReturn["historyTransactions"][$vehicle][$index]["service"]          = $data["vehicles"][$i]["historyTransactions"][$j]["transactionDetails"][$k]["service"];
                    $toReturn["historyTransactions"][$vehicle][$index]["transactionId"]    = $data["vehicles"][$i]["historyTransactions"][$j]["transactionId"];
                    $toReturn["historyTransactions"][$vehicle][$index]["unixTime"]         = $data["vehicles"][$i]["historyTransactions"][$j]["transactionTime"];
                }
            }
        }

        $veh = array();

        /**
         * Sort the transactions
         *
         * @var  $key
         * @var  $value
         */
        foreach($toReturn["historyTransactions"] as $key=>$value) {

            $veh[$key] = array_values($toReturn["historyTransactions"][$key]);

            usort($veh[$key], function ($a,$b){
                return $a['unixTime']-$b['unixTime'];
            });

            $veh[$key] = array_reverse($veh[$key]);
            $veh[$key] = array_values($veh[$key]);

        }

        $toReturn["lastVisitedStoreId"] = $data["lastVisitedStoreId"];
        $toReturn["serviceReminders"] = $data["serviceReminders"];
        $toReturn["historyTransactions"] = $veh;

        return $toReturn;
    }

}