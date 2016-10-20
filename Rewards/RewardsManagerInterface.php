<?php
namespace Acme\DataBundle\Model\Rewards;
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 12.07.2016
 * Time: 16:28
 */
interface RewardsManagerInterface
{
    /**
     * Creates an empty rewards instance, can be any class.
     *
     * @param $class
     * @return mixed
     */
    public function createRewards($class);

    /**
     * Find a user by id
     *
     * @param $userId
     * @return mixed
     */
    public function findUserById($userId);

    /**
     * Finds the counter by referral code.
     *
     * @param $referralCode
     * @return mixed
     */
    public function findByReferralCodeCounter($referralCode);

    /**
     * Finds the user by referral code.
     *
     * @param $referralCode
     * @return mixed
     */
    public function findByReferralUser($referralCode);

    /**
     * Returns a entity
     *
     * @return mixed
     */
    public function getEntity();

}