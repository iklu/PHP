<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 03.02.2017
 * Time: 11:08
 */

namespace Acme\DataBundle\EventListener;

use Acme\DataBundle\Model\Utility\DataSerializer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Acme\DataBundle\AcmeDataEvents;
use Acme\DataBundle\Model\Rewards\RewardsInterface;
use Doctrine\ORM\EntityManager;

class RewardsMobileListener implements EventSubscriberInterface
{
    /**
     * RewardsPromoListener constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            AcmeDataEvents::CHECK_IF_IS_ELIGIBLE_FOR_MOBILE_REWARDS => 'checkIfIsEligibleForMobileRewards',
            AcmeDataEvents::CHECK_IF_IS_ALREADY_REWARDED => 'checkIfIsAlreadyRewarded',
            AcmeDataEvents::SAVE_MOBILE_REWARDS_CODES => 'savePromoRewardsCode',
            AcmeDataEvents::MOBILE_REWARDS_SUCCESS => 'initializeResponseSuccess',
        );
    }

    /**
     * CHECK_IF_IS_ELIGIBLE_FOR_MOBILE_REWARDS
     *
     * Accepts ManagerInterface
     *
     * @param Event $event
     * @return mixed
     */
    public function checkIfIsEligibleForMobileRewards(Event $event)
    {

        $email = trim($event->getRequest()->get('promoCode'));

        $rewards = $event->getRewardsManager()->findByLoginEmail($email);

        if (empty($rewards)) {
            if (!empty($event->getRequest()->attributes->get('userId'))) {
                $rewards = $event->getRewardsManager()->findUserById($event->getRequest()->attributes->get('userId'));
            }
        }

        if ($rewards) {
            if (empty($rewards[0]->getChannelId())) {
                $referralData['referralCode'] = $rewards[0]->getChannelId();
                $referralData['userId'] = $rewards[0]->getId();
                $referralData['type'] = RewardsInterface::MOBILE;
                return $event->setResponse($referralData);
            } else {

                //the user is not mobile
                $event->setResponse('Promo code not found.');
                return $event->setStopped(true);
            }
        } else {
            //the user doesn't exist
            $event->setResponse('Promo code not found.');
            return $event->setStopped(true);
        }
    }


    /**
     * CHECK_IF_IS_ALREADY_REWARDED
     *
     * Accepts RewardsInterface
     *
     * @param Event $event
     * @return mixed
     */
    public function checkIfIsAlreadyRewarded(Event $event)
    {

        $rewards = $event->getRewardsManager();
        $promos = $rewards->findRewardsByRegisteredUser($event->getUserData()['userId']);

        if ($promos) {
            foreach ($promos as $key => $reward) {
                if (strtolower($reward->getPromoCode()) == "xoxomeineke") {
                    $event->setResponse('The user has code :' . $reward->getPromoCode());
                    return $event->setStopped(true);
                } elseif ($reward->getPromoType() == RewardsInterface::MOBILE) {
                    $event->setResponse('The user has been already mobile rewarded');
                    return $event->setStopped(true);
                }
            }
        }
        return $event->setResponse("The user is eligible for mobile reward.");
    }

    /**
     * Accepts PromoRewards
     *
     * SAVE_MOBILE_REWARDS_CODES
     *
     * @param Event $event
     * @return mixed
     */
    public function savePromoRewardsCode(Event $event)
    {
        $entity = $this->em->getRepository('AcmeDataBundle:Users')->findOneById($event->getUserData()['userId']);

        $rewards = $event->getRewards();
        $rewards->setRegisteredUser($entity);
        $rewards->setPromoCode("mobile-login");
        $rewards->setPromoType(RewardsInterface::MOBILE);
        $rewards->setFirstTransaction(null);

        return $event->setResponse('Success! saving to database');

    }

    /**
     * @param Event $event
     * @return mixed
     */
    public function initializeResponseSuccess(Event $event)
    {
        return $event->setResponse('success!');
    }
}