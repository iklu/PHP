<?php
namespace Acme\DataBundle\EventListener;

use Acme\DataBundle\Model\Rewards\RewardsInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Acme\DataBundle\AcmeDataEvents;
use Acme\DataBundle\Model\Utility\Curl;
use Doctrine\ORM\EntityManager;
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 12.07.2016
 * Time: 15:33
 */
class RewardsReferralListener implements EventSubscriberInterface
{
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return array(
            AcmeDataEvents::CHECK_REFERRAL_CODE_COUNTER => 'checkReferralCodeCounter',
            AcmeDataEvents::CHECK_REFERRAL_CODE_COUNTER_MAXIMUM => 'checkReferralCodeCounterMaximum',
            AcmeDataEvents::CHECK_IF_REFERRAL_USER_IS_VALID => 'checkIfReferralUserIsValid',
            AcmeDataEvents::SAVE_REFERRAL_REWARDS_CODES => 'savePromoRewardsCode',
            AcmeDataEvents::CHECK_IF_COUNTER_IS_INITIALIZED => 'checkIfCounterIsInitialized',
            AcmeDataEvents::INITIALIZE_REFERRAL_COUNTER => 'initializeReferralCounter',
            AcmeDataEvents::UPDATE_REFERRAL_COUNTER => 'updateReferralCounter',
            AcmeDataEvents::REFERRAL_CODE_SUCCESS => 'initializeResponseSuccess',
        );
    }

    /**
     * CHECK_REFERRAL_CODE_COUNTER
     *
     * @param Event $event
     * @return mixed
     */
    public function checkReferralCodeCounter(Event $event) {

        $promoCode = trim($event->getRequest()->get('promoCode'));

        $rewards = $event->getRewardsManager()->findByReferralCodeCounter($promoCode);
        if(!$rewards) {
            $event->setResponse('Promo code not found.');
            return $event->setStopped(true);
        }
        $counterData['count'] = $rewards->getCounter();
        $counterData['referralCode'] = $rewards->getPhone();

        return $event->setResponse($counterData);
    }

    /**
     * CHECK_REFERRAL_CODE_COUNTER_MAXIMUM
     * 
     * @param Event $event
     * @return mixed
     */
    public function checkReferralCodeCounterMaximum(Event $event) {

        if($event->getCounterData()['count'] >= 4) {
            $event->setResponse("This referral code has reached it's maximum limit of uses");
            return $event->setStopped(true);
        }

        $referralData['referralCode'] = $event->getCounterData()['referralCode'];
        $referralData['points'] = '8';
        $referralData['counter'] = $event->getCounterData()['count'];
        $referralData['type'] = RewardsInterface::REFERRAL;

        return $event->setResponse($referralData);
    }

    /**
     * CHECK_IF_REFERRAL_USER_IS_VALID
     *
     *
     * @param Event $event
     * @return mixed
     */
    public function checkIfReferralUserIsValid(Event $event) {

        $promoCode = trim($event->getRequest()->get('promoCode'));

        $rewards = $event->getRewardsManager()->findByReferralUser($promoCode);

        if(!$rewards) {
            $event->setResponse('Promo code not found.');
            return $event->setStopped(true);
        }

        $referralData['referralCode'] = $rewards->getPhone();
        $referralData['referralEmail'] = $rewards->getEmail();
        $referralData['points'] = '8';
        $referralData['counter'] = 1;
        $referralData['type'] = RewardsInterface::REFERRAL;

        return $event->setResponse($referralData);
    }

    /**
     * Accepts PromoRewards
     *
     * SAVE_REFERRAL_REWARDS_CODES
     *
     * @param Event $event
     * @return mixed
     */
    public function savePromoRewardsCode(Event $event) {

        $registeredUser = $this->em->getRepository('AcmeDataBundle:Users')->findOneById($event->getUserData()['userId']);
        $referralUser = $this->em->getRepository('AcmeDataBundle:Users')->findOneByPhone($event->getRequest()->get('promoCode'));

        $rewards = $event->getRewards();
        $rewards->setRegisteredUser($registeredUser);
        $rewards->setReferralUser($referralUser);
        $rewards->setPromoCode($event->getRequest()->get('promoCode'));
        $rewards->setPromoType(RewardsInterface::REFERRAL);
        $rewards->setFirstTransaction(false);
    }

    /**
     * CHECK_IF_COUNTER_INITIALIZED
     *
     * @param Event $event
     */
    public function checkIfCounterIsInitialized(Event $event) {
        
        $promoCode = $event->getRequest()->get('promoCode');

        //rewards manager handling objectManager and entities
        $counter = $event->getRewardsManager()->findByReferralCodeCounter($promoCode);

        if(!$counter)
            $event->setStopped(true);
    }


    /**
     * INITIALIZE_REFERRAL_COUNTER
     *
     * @param Event $event
     * @return mixed
     */
    public function initializeReferralCounter(Event $event) {

        $promoCode = $event->getRequest()->get('promoCode');
        $counter = $event->getRewardsManager()->getEntity();
        $counter->setPhone($promoCode);
        $counter->setCounter(1);
        $counter->setUpdateDate(new \DateTime);

    }

    /**
     * UPDATE_REFERRAL_COUNTER
     *
     * @param Event $event
     * @return mixed
     */
    public function updateReferralCounter(Event $event) {

        $promoCode = $event->getRequest()->get('promoCode');

        //rewards manager handling objectManager and entities
        $counter = $event->getRewardsManager()->findByReferralCodeCounter($promoCode);

        if(!$counter) {
            $counter = $event->getRewardsManager()->getEntity();
            $counter->setPhone($promoCode);
            $counter->setCounter(1);
            $counter->setUpdateDate(new \DateTime);

            $this->em->persist($counter);
            $this->em->flush();
            
            /** using this to stop   */
            $event->setStopped(true);
            
        } else {
            $counter->setCounter($counter->getCounter()+1);
            $counter->setUpdateDate(new \DateTime);
        }

        return $event->setResponse('Success! saving to database');
    }



    /**
     * REFERRAL_CODE_SUCCESS
     *
     * @param Event $event
     * @return mixed
     */
    public function initializeResponseSuccess(Event $event) {
        return $event->setResponse('success!');
    }
}