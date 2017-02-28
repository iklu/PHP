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
 * Time: 15:34
 */
class RewardsPromoListener implements EventSubscriberInterface
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
            AcmeDataEvents::CHECK_IF_PROMO_CODE_IS_VALID => 'checkPromoCode',
            AcmeDataEvents::CHECK_IF_USER_HAS_CREDENTIALS => 'checkIfUserHasCredentials',
            AcmeDataEvents::CLUTCH_POINTS_INITIALIZE => 'saveRewardsPointsClutch',
            AcmeDataEvents::SAVE_PROMO_REWARDS_CODES => 'savePromoRewardsCode',
            AcmeDataEvents::PROMO_CODE_SUCCESS => 'initializeResponseSuccess',
        );
    }
    
    /**
     * CHECK_IF_PROMO_CODE_IS_VALID
     * 
     * Accepts RewardsManagerInterface
     *
     * @param Event $event
     * @return mixed
     */
    public function checkPromoCode(Event $event) {

        $promoCode = strtolower(trim($event->getRequest()->get('promoCode')));

        $event->getName();
        $event->getRequest();
        
        $rewards = $event->getRewardsManager()->findByPromoCode($promoCode);
        if(!$rewards) {
            $event->setResponse('Promo code not found.');
            return $event->setStopped(true);
        }

        if(new \DateTime() > $rewards->getExpiryDate() && $rewards->getExpiryDate() != null) {
            $event->setResponse('Promo code has expired.');
            return $event->setStopped(true);
        }

        $promoData['promoCode'] = $rewards->getPromoCode();
        $promoData['points'] = $rewards->getPoints();
        $promoData['expiryDate'] = $rewards->getExpiryDate();
        $promoData['type'] = RewardsInterface::PROMO;

        return $event->setResponse($promoData);
    }

    /**
     * Accepts RewardsManagerInterface
     * Used by promo and referral
     *
     * @param Event $event
     * @return mixed
     */
    public function checkIfUserHasCredentials(Event $event){
        $event->getName();
        $event->getRequest();
        $user = $event->getRewardsManager()->findUserById($event->getUserData()['userId']);
        
        if(!$user) {
            $event->setResponse('The user was not found.');
            return $event->setStopped(true);
        }

        if(!$user[0]->getCardNumber()) {
            $event->setResponse('The user has no card number');
            return $event->setStopped(true);
        }

        $userData['cardNumber'] = $user[0]->getCardNumber();
        $userData['action'] = 'issue';
        $userData['userId'] = $user[0]->getId();
        $userData['amount']['balanceType'] = 'Points';
        
        if($event->getUserData()['type'] == RewardsInterface::PROMO || $event->getUserData()['type'] == RewardsInterface::CARD) {
            $userData['amount']['amount'] = $event->getUserData()['promoData']['points'];
        }
        
        if($event->getUserData()['type'] == RewardsInterface::REFERRAL) {
            $userData['amount']['amount'] = '8';
        }

        if($event->getUserData()['type'] == RewardsInterface::MOBILE) {
            $userData['amount']['amount'] = '0';
        }
        
        $userData['issuedBalanceExpiration'] = '2017-12-31 23:59:59';
        return $event->setResponse($userData);
    }

    /**
     * Accept RewardsManager
     *
     * @param Event $event
     * @return mixed
     */
    public function saveRewardsPointsClutch(Event $event) {
        $event->getName();
        $event->getRequest();
        $userData = $event->getUserData();
        //TODO send to clutch

        //initiate clutch service
        $clutch =  $event->getClutchService();

        $clutch->updateBalance($userData);


        return $event->setResponse($userData);
    }

    /**
     * Accepts PromoRewards
     *
     * SAVE_PROMO_REWARDS_CODES
     *
     * @param Event $event
     * @return mixed
     */
    public function savePromoRewardsCode(Event $event) {
        $entity = $this->em->getRepository('AcmeDataBundle:Users')->findOneById($event->getUserData()['userId']);

        $rewards = $event->getRewards();
        $rewards->setRegisteredUser($entity);
        $rewards->setPromoCode($event->getRequest()->get('promoCode'));
        $rewards->setPromoType(RewardsInterface::PROMO);
        $rewards->setFirstTransaction(null);

        return $event->setResponse('Success! saving to database');

    }

    /**
     * @param Event $event
     * @return mixed
     */
    public function initializeResponseSuccess(Event $event) {
        return $event->setResponse('success!');
    }


}