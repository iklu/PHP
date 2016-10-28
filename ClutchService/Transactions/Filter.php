<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 18.10.2016
 * Time: 11:28
 */

namespace Acme\DataBundle\Model\ClutchService\Transactions;


class Filter
{
    public $filter;

    public $updated = array();

    public $clutch;

    public $entityManager;

    public $transactions;

    public $cache;
  

    public function handleData($data){

        $this->clutch = $data["clutch"];
        $this->cache = $data["cache"];
        $this->entityManager = $data["entityManager"];
        $this->transactions = $data["transactionsData"];

        return   $this->transactions;
    }

    public function setUpdated($dataUpdated){
        $this->updated["clutch"] = $this->clutch;
        $this->updated["entityManager"] = $this->entityManager;
        $this->updated["cache"] = $this->cache;
        $this->updated["transactionsData"] = $dataUpdated;
    }

    public function getUpdated(){
        return $this->updated;
    }

    /**
     * @param $data
     * @param $em
     * @param $cache
     * @param $callbackFunction
     * @return array
     */
    public function callbackTransaction($data,$em="", $cache="", $callbackFunction){

        for($v = 0; $v < count($data["vehicles"]); $v++){
            for($i = 0; $i<count($data["vehicles"][$v]["historyTransactions"]); $i++){
                $data["vehicles"][$v]["historyTransactions"][$i]["transactionDetails"] = $callbackFunction($data["vehicles"][$v]["historyTransactions"][$i]["transactionDetails"], $em, $cache);
            }
        }
        return $data;
    }

    /**
     * @param $data
     * @param string $em
     * @param string $cache
     * @param string $clutch
     * @param $callbackFunction
     * @return mixed
     */
    public function callbackHistoryTransaction($data,$em="", $cache="", $clutch="", $callbackFunction){
        for($v = 0; $v < count($data["vehicles"]); $v++){
            for($i = 0; $i<count($data["vehicles"][$v]["historyTransactions"]); $i++){
                $data["vehicles"][$v]["historyTransactions"][$i] = $callbackFunction($data["vehicles"][$v]["historyTransactions"][$i], $em, $cache, $clutch);
            }
            $data["vehicles"][$v]["historyTransactions"] = array_values(array_filter($data["vehicles"][$v]["historyTransactions"]));
        }
        return $data;
    }
}