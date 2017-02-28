<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 22.08.2016
 * Time: 10:40
 */

namespace Acme\DataBundle\EventListener;


use Acme\DataBundle\Model\Utility\DataSerializer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Acme\DataBundle\AcmeDataEvents;
use Acme\DataBundle\Model\Rewards\RewardsInterface;
use Doctrine\ORM\EntityManager;

class RewardsCardNumberListener implements  EventSubscriberInterface
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
            AcmeDataEvents::CHECK_IF_CARDNUMBER_PROMO_CODE_IS_VALID => 'checkCardNumberPromoCode',
            AcmeDataEvents::SAVE_CARDNUMBER_REWARDS_CODES => 'saveCardNumberRewardsCode',
            AcmeDataEvents::CARDNUMBER_CODE_SUCCESS => 'initializeResponseSuccess'
        );
    }

    /**
     * CHECK_IF_CARDNUMBER_PROMO_CODE_IS_VALID
     *
     * Accepts RewardsManagerInterface
     *
     * @param Event $event
     * @return mixed
     */
    public function checkCardNumberPromoCode(Event $event) {


        $data = $event->getUserData();
        $promocode = strtolower(trim($data['promoCode']));
        $event->getName();
        $event->getRequest();

        //initiate clutch service
        $clutch = $event->getClutchService();

        if(!preg_match('/^(mkey|cust)_[0-9]{8}/i', $promocode ) ) { // promo code needs to be case-insensitive 8/12/16 client request
            $event->setResponse('Promo card not found.');
            return $event->setStopped(true);
        }

        if(!$clutch->getCardByCardNumber($promocode )){
            $event->setResponse('Promo code account not found.');
            return $event->setStopped(true);
        }

        $users = $event->getRewardsManager()->findUserByCardNumber($promocode);

        if(!empty($users)){
            if($users[0]->getEmail() != $event->getRequest()->get("email")){
                $event->setResponse('Promo code account already exists.');
                return $event->setStopped(true);
            }
        }

        $promoData['promoCode'] = trim($data['promoCode']); //sending here the original promo code name, not strtolower-ed variant
        $promoData['points'] = 350;
        $promoData['type'] = RewardsInterface::CARD;

        return $event->setResponse($promoData);
    }

    /**
     * Accepts PromoRewards
     *
     * SAVE_PROMO_REWARDS_CODES
     *
     * @param Event $event
     * @return mixed
     */
    public function saveCardNumberRewardsCode(Event $event) {
        $entity = $this->em->getRepository('AcmeDataBundle:Users')->findOneById($event->getUserData()['userId']);

        $rewards = $event->getRewards();
        $rewards->setRegisteredUser($entity);
        $rewards->setPromoCode($event->getRequest()->get('promoCode'));
        $rewards->setPromoType(RewardsInterface::CARD);
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