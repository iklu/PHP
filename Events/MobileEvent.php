<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 03.02.2017
 * Time: 17:50
 */

namespace Acme\DataBundle\Events;


use Acme\DataBundle\Model\Rewards\RewardsManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class MobileEvent extends MainEvent
{
    private $rewardsManager;

    private $userData;

    /**
     * MobileEvent constructor.
     * @param RewardsManagerInterface $rewardsManager
     * @param $userData
     * @param Request $request
     */
    public function __construct(RewardsManagerInterface $rewardsManager, $userData, Request $request)
    {
        parent::__construct($request);
        $this->rewardsManager = $rewardsManager;
        $this->userData = $userData;
    }

    /**
     * @return RewardsManagerInterface
     */
    public function getRewardsManager()
    {
        return $this->rewardsManager;
    }

    public function getUserData() {
        return $this->userData;
    }
}