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

class ClutchEvent extends MainEvent
{
    private $rewardsManager;

    private $userData;

    private $clutch;

    public function __construct(RewardsManagerInterface $rewardsManager, $userData,  Request $request, $clutch)
    {
        parent::__construct($request);
        $this->rewardsManager = $rewardsManager;
        $this->userData = $userData;
        $this->clutch = $clutch;
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
    public function getUserData() {
        return $this->userData;
    }

    public function getClutchService() {
        return $this->clutch;
    }
}