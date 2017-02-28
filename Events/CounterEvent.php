<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 19.07.2016
 * Time: 16:40
 */

namespace Acme\DataBundle\Events;

use Symfony\Component\HttpFoundation\Request;
use Acme\DataBundle\Model\Rewards\RewardsManagerInterface;


class CounterEvent extends MainEvent
{
    private $rewardsManager;

    private $counterData;

    public function __construct(RewardsManagerInterface $rewardsManager, $counterData,  Request $request)
    {
        parent::__construct($request);
        $this->rewardsManager = $rewardsManager;
        $this->counterData = $counterData;
    }

    /**
     * @return RewardsInterface
     */
    public function getRewardsManager()
    {
        return $this->rewardsManager;
    }

    /**
     * @return integer
     */
    public function getCounterData() {
        return $this->counterData;
    }
}