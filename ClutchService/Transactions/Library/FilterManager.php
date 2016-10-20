<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 17.10.2016
 * Time: 18:45
 */

namespace Acme\DataBundle\Model\ClutchService\Transactions\Library;


use Acme\DataBundle\Model\ClutchService\Transactions\Target;

class FilterManager
{
    public $filterChain;

    public function __construct(Target $target) {
        $this->filterChain = new FilterChain();
        $this->filterChain->setTarget($target);
    }

    public function setFilter(FilterInterface $filter){
        $this->filterChain->addFilter($filter);
    }

    public function filterTransaction($transaction){
        return $this->filterChain->execute($transaction);
    }
}