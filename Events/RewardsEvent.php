<?php
namespace Acme\DataBundle\Events;

use Symfony\Component\HttpFoundation\Request;
use Acme\DataBundle\Model\Rewards\RewardsInterface;
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 12.07.2016
 * Time: 15:34
 */
class RewardsEvent extends MainEvent
{
    private $rewards;

    private $userData;

    public function __construct(RewardsInterface $rewards, $userData, Request $request)
    {
        parent::__construct($request);
        $this->rewards = $rewards;
        $this->userData = $userData;
    }

    /**
     * @return RewardsInterface
     */
    public function getRewards()
    {
        return $this->rewards;
    }

    public function getUserData() {
        return $this->userData;
    }
 
}