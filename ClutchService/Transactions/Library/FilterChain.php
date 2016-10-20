<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 17.10.2016
 * Time: 18:47
 */

namespace Acme\DataBundle\Model\ClutchService\Transactions\Library;

use Acme\DataBundle\Model\ClutchService\Transactions\Target;

class FilterChain
{
    public $filters = array();
    public $target;

    /**
     * @param FilterInterface $filter
     */
    public function addFilter(FilterInterface $filter) {
        $this->filters[] = $filter;
    }

    /**
     * @param $transaction
     */
    public function execute($transaction) {

        $transactionToFilter = $transaction;

        foreach($this->filters as $filter) {
            $transactionToFilter = $filter->execute($transactionToFilter);
        }

        $transactionToFilter = $this->target->execute($transactionToFilter);

        return $transactionToFilter;
    }

    /**
     * @param Target $target
     */
    public function setTarget(Target $target) {
        $this->target = $target;
    }


}