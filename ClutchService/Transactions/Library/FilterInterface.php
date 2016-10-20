<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 17.10.2016
 * Time: 18:57
 */

namespace Acme\DataBundle\Model\ClutchService\Transactions\Library;


interface FilterInterface
{
    /**
     * Method executed for every filter
     *
     * @param $transaction
     * @return mixed
     */
    public function execute($transaction);
}