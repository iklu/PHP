<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 11.10.2016
 * Time: 14:46
 */

namespace Acme\DataBundle\Model\ClutchService\Account;


interface ClutchInterface
{
    public function setCardNumber($cardNumber);

    public function getCardNumber();

    public function setCustomCardNumber($customCardNumber);

    public function getCustomCardNumber();

    public function setBalance($balance);

    public function getBalance();

    public function setFirstName($firstName);

    public function getFirstName();

    public function setLastName($lastName);

    public function getLastName();

    public function setEmail($email);

    public function getEmail();

    public function setPhone($phone);

    public function getPhone();

    public function setBrandDemographics($brandDemographics);

    public function getBrandDemographics();

    public function setMailings($mailings);

    public function getMailings();
}