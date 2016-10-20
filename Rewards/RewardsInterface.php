<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 12.07.2016
 * Time: 18:32
 */

namespace Acme\DataBundle\Model\Rewards;


interface RewardsInterface
{
    const PROMO = 'PROMO';
    const REFERRAL = 'REFERRAL';
    const CARD = 'CARD';
    
    /**
     * Sets the registered user.
     *
     * @param integer $id
     *
     * @return self
     */
    public function setRegisteredUser(\Acme\DataBundle\Entity\Users $registeredUser = null);

    /**
     * Gets the registered user.
     *
     * @return string
     */
    public function getRegisteredUser();

    /**
     * Sets the referral  user.
     *
     * @param integer $id
     *
     * @return self
     */
    public function setReferralUser(\Acme\DataBundle\Entity\Users $registeredUser = null);

    /**
     * Gets the referral user.
     *
     * @return string
     */
    public function getReferralUser();

    /**
     * Sets the promo code.
     *
     * @param string $promoCode
     *
     * @return self
     */
    public function setPromoCode($promoCode);

    /**
     * Gets the promo code.
     *
     * @return string
     */
    public function getPromoCode();

    /**
     * Sets the promo type.
     *
     * @param string $promoType
     *
     * @return self
     */
    public function setPromoType($promoType);

    /**
     * Gets the promo type.
     *
     * @return string
     */
    public function getPromoType();

    /**
     * @param boolean $boolean
     *
     * @return self
     */
    public function setFirstTransaction($boolean);

    /**
     * Gets the first transaction.
     *
     * @return boolean
     */
    public function getFirstTransaction();


    /**
     * Sets the registration date.
     *
     * @param \DateTime $time
     *
     * @return self
     */
    public function setRegistrationDate(\DateTime $time = null);
    
}