<?php

/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 11.10.2016
 * Time: 10:51
 */
namespace Acme\DataBundle\Model\ClutchService\Transactions;

use Acme\DataBundle\Model\ClutchService\Transactions\Library\FilterManager;

class Transaction extends TransactionManager
{

    /**
     * @var
     */
    public $filterManager;

    /**
     * @param FilterManager $filterManager
     */
    public function setFilterManager(FilterManager $filterManager){
        $this->filterManager = $filterManager;
    }

    /**
     * Handle transaction
     *
     */
    function handleTransaction() {
        return $this->filterManager->filterTransaction($this->formatTransaction());
    }

}