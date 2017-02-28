<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 13.07.2016
 * Time: 12:24
 */

namespace Acme\DataBundle\Events;

use Symfony\Component\HttpFoundation\Request;
use Acme\DataBundle\Model\Rewards\RewardsManagerInterface;

class ManagerEvent extends MainEvent
{
    private $rewardsManager;

    public function __construct(RewardsManagerInterface $rewardsManager,  Request $request)
    {
        parent::__construct($request);
        $this->rewardsManager = $rewardsManager;
    }

    /**
     * @return RewardsInterface
     */
    public function getRewardsManager()
    {
        return $this->rewardsManager;
    }  
}