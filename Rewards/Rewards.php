<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 13.07.2016
 * Time: 16:02
 */
namespace Acme\DataBundle\Model\Rewards;

use Acme\DataBundle\AcmeDataEvents;
use Acme\DataBundle\Events\ClutchEvent;
use Acme\DataBundle\Events\RewardsEvent;
use Acme\DataBundle\Events\ManagerEvent;
use Acme\DataBundle\Events\CounterEvent;


class Rewards
{
    private $dispatcher;

    private $rewardsManager;

    private $container;

    private $clutch;

    public function __construct($container)  {

        $this->container = $container;

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $this->dispatcher = $this->container->get('event_dispatcher');

        /** @var $rewardsManager \Acme\DataBundle\Model\Rewards\RewardsManagerInterface */
        $this->rewardsManager = $this->container->get('meineke.rewards_manager');

        /** @var  clutch */
        $this->clutch = $this->container->get("meineke.clutch_service");
    }

    /**
     * This method is used to manage the promo codes
     *
     * @param $userId
     * @param $request
     */
    public  function promoCodes($userId, $request){

        /** @var $event  check if promo code is valid  */
        $event = $this->checkIfPromoCodeIsValid($request);
     
        if(!$event->stopped()){

            /** The user id of the new registered user */
            $userData['userId'] = $userId;
            $userData['type'] = RewardsInterface::PROMO;
            $userData['promoData'] =  $event->getResponse();

            /** get the entity Users */
            $this->rewardsManager->createRewards('AcmeDataBundle:Users');

            /** @var $event  (inject manager to ClutchEvent class for managing Users entity)*/
            $event = new ClutchEvent($this->rewardsManager, $userData, $request, $this->clutch);

            /** Dispatch event */
            $this->dispatcher->dispatch(AcmeDataEvents::CHECK_IF_USER_HAS_CREDENTIALS, $event);
            
            if(!$event->stopped()) {

                /** @var  $userData from CHECK_IF_USER_HAS_CREDENTIALS  *//** Dispatch event */
                $userData = $event->getResponse();

                /** get Clutch parameters */
                $userData['clutch'] = $this->container->getParameter('clutch');

                /** @var  $event  (use rewards manager manager in ClutchEvent class for sending the data to Clutch) */
                $event = new ClutchEvent($this->rewardsManager, $userData , $request, $this->clutch);

                /** Dispatch event */
                $this->dispatcher->dispatch(AcmeDataEvents::CLUTCH_POINTS_INITIALIZE, $event);

                if(!$event->stopped()) {

                    /** @var  $userData from CLUTCH_POINTS_INITIALIZE  */
                    $userData = $event->getResponse();

                    /** get the entity PromoRewards */
                    $rewards  = $this->rewardsManager->createRewards('AcmeDataBundle:PromoRewards');

                    /** @var  $event  (inject manager to RewardsEvent class to save the new promo reward in PromoRewards entity) */
                    $event = new RewardsEvent($rewards, $userData, $request);

                    /** Dispatch event */
                    $this->dispatcher->dispatch(AcmeDataEvents::SAVE_PROMO_REWARDS_CODES, $event);

                    /** flush PromoRewards  */
                    $this->rewardsManager->updateRewards($rewards);

                    if(!$event->stopped()) {

                        /** Dispatch event */
                        $this->dispatcher->dispatch(AcmeDataEvents::PROMO_CODE_SUCCESS, $event);
                    }
                }
            }
        }
    }

    /**
     * This method is used to manage the card number codes
     *
     * @param $userId
     * @param $request
     */
    public  function cardNumberCodes($userId, $request){

        /** @var $event  check if card number code is valid  */
        $event = $this->checkIfCardNumberPromoCodeIsValid($request);

        if(!$event->stopped()){

            /** The user id of the new registered user */
            $userData['userId'] = $userId;
            $userData['type'] = RewardsInterface::CARD;
            $userData['promoData'] =  $event->getResponse();

            /** get the entity Users */
            $this->rewardsManager->createRewards('AcmeDataBundle:Users');

            /** @var $event  (inject manager to ClutchEvent class for managing Users entity)*/
            $event = new ClutchEvent($this->rewardsManager, $userData, $request, $this->clutch);

            /** Dispatch event */
            $this->dispatcher->dispatch(AcmeDataEvents::CHECK_IF_USER_HAS_CREDENTIALS, $event);

            if(!$event->stopped()) {

                /** @var  $userData from CHECK_IF_USER_HAS_CREDENTIALS  *//** Dispatch event */
                $userData = $event->getResponse();

                /** get Clutch parameters */
                $userData['clutch'] = $this->container->getParameter('clutch');

                /** @var  $event  (use rewards manager manager in ClutchEvent class for sending the data to Clutch) */
                $event = new ClutchEvent($this->rewardsManager, $userData , $request, $this->clutch);

                /** Dispatch event */
                $this->dispatcher->dispatch(AcmeDataEvents::CLUTCH_POINTS_INITIALIZE, $event);

                if(!$event->stopped()) {

                    /** @var  $userData from CLUTCH_POINTS_INITIALIZE  */
                    $userData = $event->getResponse();

                    /** get the entity PromoRewards */
                    $rewards  = $this->rewardsManager->createRewards('AcmeDataBundle:PromoRewards');

                    /** @var  $event  (inject manager to RewardsEvent class to save the new promo reward in PromoRewards entity) */
                    $event = new RewardsEvent($rewards, $userData, $request);

                    /** Dispatch event */
                    $this->dispatcher->dispatch(AcmeDataEvents::SAVE_CARDNUMBER_REWARDS_CODES, $event);

                    /** flush PromoRewards  */
                    $this->rewardsManager->updateRewards($rewards);

                    if(!$event->stopped()) {

                        /** Dispatch event */
                        $this->dispatcher->dispatch(AcmeDataEvents::CARDNUMBER_CODE_SUCCESS, $event);
                    }
                }
            }
        }
    }

    /**
     * This method is used to manage referral codes (user phone number)
     *
     * @param $userId
     * @param $request
     */
    public function referralCodes($userId, $request) {

        /** @var $event  check if promo code is valid  */
        $event = $this->checkIfReferralCodeIsValid($request);

        /** if the result is ok continue with the points registration */
        if(!$event->stopped()) {

            $counterData = $event->getResponse();

            /** The user id of the new registered user */
            $userData['userId'] = $userId;
            $userData['type'] = RewardsInterface::REFERRAL;

            /** get the entity Users */
            $this->rewardsManager->createRewards('AcmeDataBundle:Users');

            /** @var $event  (inject manager to ClutchEvent class for managing Users entity)*/
            $event = new ClutchEvent($this->rewardsManager, $userData, $request, $this->clutch);

            /** Dispatch event */
            $this->dispatcher->dispatch(AcmeDataEvents::CHECK_IF_USER_HAS_CREDENTIALS, $event);

            if(!$event->stopped()) {

                /** @var  $userData from CHECK_IF_USER_HAS_CREDENTIALS  */
                $userData = $event->getResponse();

                /** get Clutch parameters */
                $userData['clutch'] = $this->container->getParameter('clutch');

                /** @var  $event  (use rewards manager manager in ClutchEvent class for sending the data to Clutch) */
                $event = new ClutchEvent($this->rewardsManager, $userData , $request , $this->clutch);

                /** Dispatch event */
                $this->dispatcher->dispatch(AcmeDataEvents::CLUTCH_POINTS_INITIALIZE, $event);

                if(!$event->stopped()) {

                    /** @var  $userData from CLUTCH_POINTS_INITIALIZE  */
                    $userData = $event->getResponse();

                    /** get the entity PromoRewards */
                    $rewards  = $this->rewardsManager->createRewards('AcmeDataBundle:PromoRewards');

                    /** @var  $event  (inject manager to RewardsEvent class to save the new promo reward in PromoRewards entity) */
                    $event = new RewardsEvent($rewards, $userData, $request);

                    /** Dispatch event */
                    $this->dispatcher->dispatch(AcmeDataEvents::SAVE_REFERRAL_REWARDS_CODES, $event);

                    /** flush PromoRewards  */
                    $this->rewardsManager->updateRewards($rewards);

                    if(!$event->stopped()) {

                        /** get the entity ReferralCounter */
                        $counter = $this->rewardsManager->createRewards('AcmeDataBundle:ReferralCounter');

                        /** @var $event  (inject manager to CounterEvent class for updating the counter)*/
                        $event = new CounterEvent($this->rewardsManager, $counterData, $request);

                        /** Dispatch event */
                        $this->dispatcher->dispatch(AcmeDataEvents::CHECK_IF_COUNTER_IS_INITIALIZED, $event);

                        /** stopping event in listener because is inserting and updating in same objectManager */
                        if(!$event->stopped()) {
                            /** Dispatch event */
                            $this->dispatcher->dispatch(AcmeDataEvents::UPDATE_REFERRAL_COUNTER, $event);

                            /** flush ReferralCounter  */
                            $this->rewardsManager->updateCounter($counter);
                        } else {
                            /** Dispatch event */
                            $this->dispatcher->dispatch(AcmeDataEvents::INITIALIZE_REFERRAL_COUNTER, $event);

                            /** flush ReferralCounter  */
                            $this->rewardsManager->createCounter($counter);
                        }

                        /** Dispatch event */
                        $this->dispatcher->dispatch(AcmeDataEvents::REFERRAL_CODE_SUCCESS, $event);
                    }
                }
            }
        }
    }
	
    /**
     * @param $request
     * @return ClutchEvent
     */
    public function checkIfCardNumberPromoCodeIsValid($request){

        /** get the entity PromoCodes */
        $this->rewardsManager->createRewards('AcmeDataBundle:Users');
        
        $promoData['promoCode'] = $request->get('promoCode');
        $promoData['clutch'] = $this->container->getParameter('clutch');
        
        /** @var $event  (inject manager to ManagerEvent class for managing Users entity)*/
        $event = new ClutchEvent($this->rewardsManager, $promoData , $request, $this->clutch);

        /** Dispatch event */
        $this->dispatcher->dispatch(AcmeDataEvents::CHECK_IF_CARDNUMBER_PROMO_CODE_IS_VALID, $event);

        return $event;
    }

    /**
     * @param $request
     * @return ManagerEvent
     */
    public function checkIfPromoCodeIsValid($request){

        /** get the entity PromoCodes */
        $this->rewardsManager->createRewards('AcmeDataBundle:PromoCodes');

        /** @var $event  (inject manager to ManagerEvent class for managing PromoCodes entity)*/
        $event = new ManagerEvent($this->rewardsManager, $request );

        /** Dispatch event */
        $this->dispatcher->dispatch(AcmeDataEvents::CHECK_IF_PROMO_CODE_IS_VALID, $event);

        return $event;
    }

    /**
     * @param $request
     * @return ManagerEvent
     */
    public function checkIfReferralCodeIsValid($request){

        /** get the entity ReferralCounter */
        $this->rewardsManager->createRewards('AcmeDataBundle:ReferralCounter');

        $event = new ManagerEvent($this->rewardsManager , $request);
        $this->dispatcher->dispatch(AcmeDataEvents::CHECK_REFERRAL_CODE_COUNTER, $event);

        //the counter has been found
        if(!$event->stopped()) {

            /** @var  $counterData from CHECK_REFERRAL_CODE_COUNTER  */
            $counterData = $event->getResponse();

            /** @var  $event  (use rewards manager manager in CounterEvent to check how many times the referral code has been used) */
            $event = new CounterEvent($this->rewardsManager , $counterData , $request);

            /** Dispatch event */
            $this->dispatcher->dispatch(AcmeDataEvents::CHECK_REFERRAL_CODE_COUNTER_MAXIMUM, $event);

        // the counter not found search in users table
        } else {
            /** get the entity Users */
            $this->rewardsManager->createRewards('AcmeDataBundle:Users');

            /** @var  $event */
            $event = new ManagerEvent($this->rewardsManager , $request);

            /** Dispatch event */
            $this->dispatcher->dispatch(AcmeDataEvents::CHECK_IF_REFERRAL_USER_IS_VALID, $event);
        }

        return $event;
    }
}